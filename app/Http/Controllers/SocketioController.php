<?php

namespace App\Http\Controllers;

use App\Models\MatchScore;
use App\Models\MatchTeam;
use App\Models\SportMatch;
use Illuminate\Http\Request;
use PHPSocketIO\SocketIO;
use Workerman\Worker;

class SocketioController extends Controller
{
    //

    public function index()
    {
        $context = [];
        // SSL context
        // $context = array(
        //     'ssl' => array(
        //         'local_cert'  => '/your/path/of/server.pem',
        //         'local_pk'    => '/your/path/of/server.key',
        //         'verify_peer' => false
        //     )
        // );
        $io = new SocketIO(config('socket_io.port'), $context);

        $io->on('connection', function($socket) use ($io){
            // You can get the socket id using $socket->id
            // You can get the querystring parameters passed from the client using $socket->handshake['query']
            // Here you can bind this socket id with user and save socket id into your database
            $io->emit('connection_established', ['socket_id'=>$socket->id]);
            // print('New Socket Connection Established: '.$socket->id);

            $socket->on('login_on_socket', function($data, $ack) use ($socket, $io){
                $auth_token = $data['auth_token'];
            });

            // This event will call when someone emit broadcast_message event
            $socket->on('live_score', function($data, $ack) use ($socket, $io){
                $sport_id = $data['sport_id'];
                $start_date = date('Y-m-d 00:00:00');
                $end_date = date('Y-m-d 23:59:59');
                $live_scores = SportMatch::select('matches.id', 'match_vs', 'start_date', 'end_date')->whereBetween('end_date', [$start_date, $end_date]);
                $live_scores->join('serieses as series', 'series.id', '=', 'matches.series_id');
                $live_scores->where('series.sport_id', $sport_id);
                $live_scores->with('matchTeams', function($query){
                    $query->select('id', 'match_id', 'team_name');
                    $query->with('matchScore', function($query1) {
                        $query1->select('id', 'match_team_id', 'score', 'match_over', 'wicket', 'innings');
                    });
                });
                $live_scores = $live_scores->get();

                $ack(['message'=>'Live Score', 'matches' => $live_scores]);
                print('Socket Broadcast Event Emitted: '.$socket->id);
            });

            // This event will call when any socket connection gets disconnected
            $socket->on('disconnect', function() use ($socket, $io){
                // $user = User::where('socket_id', $socket->id)->first();
                // if(!empty($user)) {
                //     $user->socket_id = '';
                //     $user->save();
                // }
                $io->emit('connection_disconnected', ['socket_id'=>$socket->id]);
                print('Socket Connection Disconnected: '.$socket->id);
            });
        });

        Worker::runAll();
    }
}
