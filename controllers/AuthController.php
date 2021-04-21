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

    /**
     * AuthController constructor.
     * @param User $user instantiation of user object
     * @param string $requestMethod the HTTP request type
     */
    public function __construct(User $user, string $requestMethod)
    {
        $this->requestMethod = $requestMethod;
        $this->user = $user;
    }

    /**
     * Routes all HTTP requests to the appropriate functions - this must be called after the object is created.
     */
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

    /**
     * Handles the verification of user credentials (username and password) which are passed through using
     * php://input.
     *
     * @return array containing user data
     */
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


    /**
     * Used when a user requests an invalid item/endpoint.
     *
     * @return array containing the appropriate HTTP response header
     */
    private function notFound(): array
    {
        $response['status_code_header'] = self::HTTP_NOT_FOUND;
        return $response;
    }

    /**
     * Used when a user requests tries to access an endpoint without having been authenticated.
     *
     * @return array containing the appropriate HTTP response header
     */
    private function notAuthenticated(): array
    {
        $response['status_code_header'] = self::HTTP_FORBIDDEN;
        return $response;
    }

    /**
     * Used when a user requests tries to access an endpoint which they do not have permission to access.
     *
     * @return array containing the appropriate HTTP response header
     */
    private function notAuthorized(): array
    {
        $response['status_code_header'] = self::HTTP_UNAUTHORIZED;
        return $response;
    }

}