<?php

namespace api;

include_once '../../autoload.php';
include_once '../../helpers/config.php';

use helpers\Database;
use http\Client;
use PHPUnit\Framework\TestCase;

class FightAPITest extends TestCase
{
    private $client;
    private $url = '';
    private $api;
    private $apiId;

    public function setUp(): void
    {
        $base_url = BASE_URL;

        $db = (new Database())->getConnection();
        $this->api = new \models\APIAccess($db);

        $this->api->setEndDate('2030-01-01');
        $this->api->setStartDate('2020-01-01');
        $this->api->setApiKey('random12345');
        $this->api->create();
        $this->apiId = $this->api->getApiId();

        $apiKey = http_build_query(['apiKey' => $this->api->getApiKey()]);

        $this->url = API_URL  . $apiKey;

        $client = new Client($base_url, array(
            'request.options' => array(
                'exceptions' => false,
            )
        ));
    }

    public function tearDown(): void
    {
        $this->api->delete($this->apiId);
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
