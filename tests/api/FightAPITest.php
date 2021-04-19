<?php


namespace api;

use http\Client;
use PHPUnit\Framework\TestCase;

chdir(__DIR__);

class FightAPITest extends TestCase
{
    private Client $client;
    private string $url = '';
    private \APIAccess $api;

    public function setUp(): void
    {
        $base_url = 'http://localhost:8888/promma';

        $db = (new \Database())->getConnection();
        $this->api = new \APIAccess($db);

        $this->api->setEndDate('2030-01-01');
        $this->api->setStartDate('2020-01-01');
        $this->api->setApiKey('random12345');
        $this->api->create();

        $api_path = '/api/' . $this->api->getApiKey();

        $this->url = $base_url . $api_path;

        $client = $client = new Client($base_url, array(
            'request.options' => array(
                'exceptions' => false,
            )
        ));
    }

    public function tearDown(): void
    {
        $this->api->delete();
    }

    public function testCreateValid() {
        $api_endpoint = "/fight/create";
        $data = array(
            'EventID' => rand(1,500),
            'RefereeID' => rand(1,6),
            'TitleBout' => rand(0, 1),
            'WeightClassID' => rand(1,14),
            'NumOfRounds' => rand(3,5),
            'AthleteID1' => rand(1,3314),
            'AthleteID2' => rand(1,3314)
        );

        $request = $this->client->post(($this->url . $api_endpoint), null, json_encode($data));
        $response = $request->send();
    }
}
