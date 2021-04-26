<?php

namespace templates;

include_once '../../helpers/config.php';
require '../../vendor/autoload.php'; // composer autoload

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class RegisterTest extends TestCase
{
    private $client;
    private $url;

    private $userNameValid;
    private $passwordValid;
    private $emailValid;
    private $firstNameValid;
    private $lastNameValid;
    private $dobValid;
    private $userNameInvalid;
    private $passwordInvalid;
    private $emailInvalid;
    private $firstNameInvalid;
    private $lastNameInvalid;
    private $dobInvalid;

    public function setUp(): void
    {
        $queryString = http_build_query(['page' => 'register']);

        $this->url = BASE_URL . '/?' . $queryString;

        $this->userNameValid = 'usernameValid';
        $this->passwordValid = 'passwordValid';
        $this->emailValid = 'emailValid@email.com';
        $this->firstNameValid = 'firstNameValid';
        $this->lastNameValid = 'lastNameValid';
        $this->dobValid = '01/01/1990';

        $this->userNameInvalid = '';
        $this->passwordInvalid = '';
        $this->emailInvalid = 'notAValidEmail';
        $this->firstNameInvalid = '';
        $this->lastNameInvalid = '';
        $this->dobInvalid = '33/33/3333';


        $this->client = new Client();
    }

    public function tearDown(): void
    {

    }

    public function testCreateInvalidData()
    {
        $data = [
            'UserName' => $this->userNameInvalid,
            'UserPassword' => $this->passwordInvalid,
            'UserPasswordConfirm' => $this->passwordInvalid,
            'UserEmail' => $this->emailInvalid,
            'UserFirstName' => $this->firstNameInvalid,
            'UserLastName' => $this->lastNameInvalid,
            'UserDOB' => $this->dobInvalid
        ];


        $response = $this->client->request('POST', $this->url, [
            'form_params' => $data
        ]);

        $body = $response->getBody()->getContents();

        self::assertTrue(stripos($body, 'Field UserName must be populated') !== false);
        self::assertTrue(stripos($body, 'Field UserPassword must be populated') !== false);
        self::assertTrue(stripos($body, 'Field UserPasswordConfirm must be populated') !== false);
        self::assertTrue(stripos($body, 'Field UserFirstName must be populated') !== false);
        self::assertTrue(stripos($body, 'Field UserLastName must be populated') !== false);
    }

    public function testCreateInvalidPasswordsDoNotMatch()
    {
        $data = [
            'UserName' => $this->userNameInvalid,
            'UserPassword' => $this->passwordValid,
            'UserPasswordConfirm' => $this->passwordInvalid,
            'UserEmail' => $this->emailInvalid,
            'UserFirstName' => $this->firstNameInvalid,
            'UserLastName' => $this->lastNameInvalid,
            'UserDOB' => $this->dobInvalid
        ];


        $response = $this->client->request('POST', $this->url, [
            'form_params' => $data
        ]);

        $body = $response->getBody()->getContents();

        self::assertTrue(stripos($body, 'Passwords do not match') !== false);
    }


    public function testCreateValidData()
    {
        $data = [
            'UserName' => $this->userNameValid,
            'UserPassword' => $this->passwordValid,
            'UserPasswordConfirm' => $this->passwordValid,
            'UserEmail' => $this->emailValid,
            'UserFirstName' => $this->firstNameValid,
            'UserLastName' => $this->lastNameValid,
            'UserDOB' => $this->dobValid
        ];


        $response = $this->client->request('POST', $this->url, [
            'form_params' => $data
        ]);

        $body = $response->getBody()->getContents();
        print_r($body);

        self::assertFalse(stripos($body, 'Field UserName must be populated'));
        self::assertFalse(stripos($body, 'Field UserPassword must be populated'));
        self::assertFalse(stripos($body, 'Field UserPasswordConfirm must be populated'));
        self::assertFalse(stripos($body, 'Field UserFirstName must be populated'));
        self::assertFalse(stripos($body, 'Field UserLastName must be populated'));
    }
}
