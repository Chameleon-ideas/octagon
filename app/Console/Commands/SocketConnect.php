<?php

namespace App\Console\Commands;

use App\Http\Controllers\SocketioController;
use Illuminate\Console\Command;

class SocketConnect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket:run {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Workerman SocketIO Integration';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // return Command::SUCCESS;
        $result = (new SocketioController)->index();
    }
}
