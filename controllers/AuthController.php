<?php

namespace controllers;

use PDO;
use models\User;


class AuthController
{
    const HTTP_SUCCESS = 'HTTP/1.1 200 OK';
    const HTTP_CREATED = 'HTTP/1.1 201 Created';
    const HTTP_SUCCESS_NO_CONTENT = 'HTTP/1.1 204 No Content'; // update and delete
    const HTTP_BAD_REQUEST = 'HTTP/1.1 400 Bad Request';
    const HTTP_UNAUTHORIZED = 'HTTP/1.1 401 Unauthorized';
    const HTTP_NOT_FOUND = 'HTTP/1.1 404 Not Found';
    const HTTP_FORBIDDEN = 'HTTP/1.1 403 Forbidden';

    private User $user;
    private string $requestMethod;

    public function __construct(User $user, string $request)
    {
        $this->requestMethod = $request;
        $this->user = $user;
    }

    public function process_request()
    {
        switch ($this->requestMethod) {
            case 'POST':
                // login
                $response = $this->login();
                break;
            default:
                $response = $this->notFound();
                break;
        }

        header($response['status_code_header']);
        if (isset($response['body']) && $response['body']) {
            echo json_encode($response['body']);
        }
    }

    private function login(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['Username']) || !isset($data['Password'])) {
            $response['status_code_header'] = self::HTTP_BAD_REQUEST;
            $response['body'] = ['Error' => 'Username and/or password missing'];
            return $response;
        }

        $username = $data['Username'];
        $password = $data['Password'];

        $result = $this->user->verifyLoginCredentials($username, $password);

        if (!$result) {
            $response['status_code_header'] = self::HTTP_UNAUTHORIZED;
            $response['body'] = ['Error' => 'Username and password incorrect'];
            return $response;
        }

        $response['status_code_header'] = self::HTTP_SUCCESS;
        $response['body'] = $result;
        return $response;
    }



    private function notFound(): array
    {
        $response['status_code_header'] = self::HTTP_NOT_FOUND;
        return $response;
    }

    private function notAuthenticated(): array
    {
        $response['status_code_header'] = self::HTTP_FORBIDDEN;
        return $response;
    }

    private function notAuthorized(): array
    {
        $response['status_code_header'] = self::HTTP_UNAUTHORIZED;
        return $response;
    }

}