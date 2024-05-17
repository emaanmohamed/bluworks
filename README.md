Please take in consideration these actions:
1- composer install
2- add db name in .env
3- run migrations and seed -> I added dummy data for testing perspective

note:

best practise for getting latitude and longitude to integrate with google but I havn't subscription but any ways function like

  public function getCoordinates()
    {
        $response = $this->client->get("https://maps.googleapis.com/maps/api/geocode/json", [
            'query' => [
                'address' => 'your address',
                'key' => $this->apiKey
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        dd($data);
        if ($data['status'] === 'OK') {
            $location = $data['results'][0]['geometry']['location'];
            dd($location);
            return [
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
            ];
        }
    }



    but for testing, I put latitude and longitude hard-coded  
