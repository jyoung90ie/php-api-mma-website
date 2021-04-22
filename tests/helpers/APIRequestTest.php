<?php

namespace helpers;

require_once '../../autoload.php';

use PHPUnit\Framework\TestCase;

class APIRequestTest extends TestCase
{
    private string $apiUrl;
    private string $eventEndpoint;
    private string $athleteEndpoint;
    private string $fightEndpoint;
    private string $userEndpoint;
    private APIRequest $apiRequest;


    protected function setUp(): void
    {
        $this->apiUrl = "http://localhost:8888/promma/api";
        $this->eventEndpoint = "/event";
        $this->athleteEndpoint = "/athlete";
        $this->fightEndpoint = "/fight";
        $this->userEndpoint = "/user";
    }

    public function testFetchResponseEventAllValid(): void
    {
        $apiModule = $this->eventEndpoint;
        $queryStrings = ['page' => 'events', 'start' => 10];
        $apiRequest = new APIRequest($this->apiUrl, $apiModule, null, $queryStrings);

        $response = $apiRequest->fetchApiData();

        $apiRequest->displayPagination();

        self::assertFalse(isset($response['Error']));

        self::assertTrue(isset($response['totalResults']));
        self::assertTrue(isset($response['currentResults']));
        self::assertTrue(isset($response['data']));
    }

    public function testFetchResponseEventOneValid(): void
    {
        $apiModule = $this->eventEndpoint;
        $itemId = 1;
        $apiRequest = new APIRequest($this->apiUrl, $apiModule, $itemId);

        $response = $apiRequest->fetchApiData();

        self::assertFalse(isset($response['Error']));

        self::assertTrue(isset($response['EventID']));
        self::assertTrue(isset($response['Fights']));
    }

    public function testFetchResponseEventOneInvalid(): void
    {
        $apiModule = "/events";
        $itemId = 99999;
        $apiRequest = new APIRequest($this->apiUrl, $apiModule, $itemId);

        $response = $apiRequest->fetchApiData();

        self::assertTrue(isset($response['Error']));
        self::assertFalse(isset($response['EventID']));

        $apiModule = $this->eventEndpoint;
        $itemId = -1;
        $apiRequest = new APIRequest($this->apiUrl, $apiModule, $itemId);

        $response = $apiRequest->fetchApiData();

        var_dump($response);

        self::assertTrue(isset($response['Error']));
        self::assertFalse(isset($response['EventID']));


        $itemId = "invalid";

        // expect itemId to through typeError exception as it's not an int
        $this->expectException(\TypeError::class);
        $apiRequest = new APIRequest($this->apiUrl, $apiModule, $itemId);
    }

    // athletes
    public function testFetchResponseAthleteAllValid(): void
    {
        $apiModule = $this->athleteEndpoint;
        $apiRequest = new APIRequest($this->apiUrl, $apiModule);

        $response = $apiRequest->fetchApiData();

        self::assertFalse(isset($response['Error']));

        self::assertTrue(isset($response['totalResults']));
        self::assertTrue(isset($response['currentResults']));
        self::assertTrue(isset($response['data']));
    }

    public function testFetchResponseAthleteOneValid(): void
    {
        $apiModule = $this->athleteEndpoint;
        $itemId = 1;
        $apiRequest = new APIRequest($this->apiUrl, $apiModule, $itemId);

        $response = $apiRequest->fetchApiData();

        self::assertFalse(isset($response['Error']));

        self::assertTrue(isset($response['AthleteID']));
        self::assertTrue(isset($response['Fights']));
    }

    public function testFetchResponseAthleteOneInvalid(): void
    {
        $apiModule = "/athletes";
        $itemId = 99999;
        $apiRequest = new APIRequest($this->apiUrl, $apiModule, $itemId);

        $response = $apiRequest->fetchApiData();

        self::assertTrue(isset($response['Error']));
        self::assertFalse(isset($response['EventID']));

        $apiModule = $this->athleteEndpoint;
        $itemId = -1;
        $apiRequest = new APIRequest($this->apiUrl, $apiModule, $itemId);

        $response = $apiRequest->fetchApiData();

        self::assertTrue(isset($response['Error']));
        self::assertFalse(isset($response['EventID']));


        $itemId = "invalid";

        // expect itemId to through typeError exception as it's not an int
        $this->expectException(\TypeError::class);
        $apiRequest = new APIRequest($this->apiUrl, $apiModule, $itemId);
    }

}
