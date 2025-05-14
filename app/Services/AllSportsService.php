<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AllSportsService
{
    /**
     * @param $endpoint
     * @param array $reqData
     * @param bool $header
     * @return array
     */
    private function getEndpointData($endpoint, $reqData = [], $header = true): array
    {
        $reqData['APIkey'] = '725a78db19c6fd62b9e0b3b10fa1a77ec081a0d1ec2e2b60075c0ea99492fa91';
        $response = Http::acceptJson();
//        if ($header) {
//            $response = $response->withHeaders([env('API_AUTH_HEADER') => session('access_token')]);
//        }
        $response = $response->timeout(25)->asForm()->get('https://apiv2.allsportsapi.com/' . $endpoint, $reqData);
        return $response->json() ?? [];
    }

    public function getFootballCountries($reqData = [])
    {
        $reqData['met'] = 'Countries';
        return $this->getEndpointData('football', $reqData);
    }

    public function getFootballLeagues($reqData = [])
    {
        $reqData['met'] = 'Leagues';
        return $this->getEndpointData('football', $reqData);
    }

    public function getFootballTeams($reqData = [])
    {
        $reqData['met'] = 'Teams';
        return $this->getEndpointData('football', $reqData);
    }

    public function getBasketballCountries($reqData = [])
    {
        $reqData['met'] = 'Countries';
        return $this->getEndpointData('basketball', $reqData);
    }

    public function getBasketballLeagues($reqData = [])
    {
        $reqData['met'] = 'Leagues';
        return $this->getEndpointData('basketball', $reqData);
    }

    public function getBasketballTeams($reqData = [])
    {
        $reqData['met'] = 'Teams';
        return $this->getEndpointData('basketball', $reqData);
    }

    public function getCricketLeagues($reqData = [])
    {
        $reqData['met'] = 'Leagues';
        return $this->getEndpointData('cricket', $reqData);
    }

    public function getCricketTeams($reqData = [])
    {
        $reqData['met'] = 'Teams';
        return $this->getEndpointData('cricket', $reqData);
    }
}
