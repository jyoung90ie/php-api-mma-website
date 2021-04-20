<?php

namespace controllers;

use PDO;
use models\User;


class Controller
{
    const HTTP_SUCCESS = 'HTTP/1.1 200 OK';
    const HTTP_CREATED = 'HTTP/1.1 201 Created';
    const HTTP_SUCCESS_NO_CONTENT = 'HTTP/1.1 204 No Content'; // update and delete
    const HTTP_UNAUTHORIZED = 'HTTP/1.1 401 Unauthorized';
    const HTTP_NOT_FOUND = 'HTTP/1.1 404 Not Found';
    const HTTP_FORBIDDEN = 'HTTP/1.1 403 Forbidden';

    private ?int $moduleId;
    private User $user;
    private string $requestMethod;
    private object $module;

    public function __construct(object $module, User $user, string $request, ?int $moduleId)
    {
        $this->requestMethod = $request;
        $this->moduleId = $moduleId;
        $this->user = $user;
        $this->module = $module;
    }

    public function process_request()
    {
        switch ($this->requestMethod) {
            case 'POST':
                // create
                $response = $this->create();
                break;
            case 'GET':
                // read
                if (!is_null($this->moduleId)) {
                    $response = $this->getOne($this->moduleId);
                } else {
                    $response = $this->getAll();
                }
                break;
            case 'PUT':
                // update
                $response = $this->update($this->moduleId);
                break;
            case 'DELETE':
                // delete
                $response = $this->delete($this->moduleId);
                break;
            default:
                $response = $this->notFound();
        }

        header($response['status_code_header']);
        if (isset($response['body']) && $response['body']) {
            echo $response['body'];
        }
    }

    private function getAll(): array
    {
        if (!$this->user->hasPermission($this->module::PERMISSION_AREA, 'READ')) {
            return $this->notAuthorized();
        }

        $result = $this->module->getAll();
        $response['status_code_header'] = self::HTTP_SUCCESS;
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getOne(int $id): array
    {
        if (!$this->user->hasPermission($this->module::PERMISSION_AREA, 'READ')) {
            return $this->notAuthorized();
        }

        $result = $this->module->getOne($id);
        if (!$result) {
            // fight doesn't exist
            return $this->notFound();
        }
        $response['status_code_header'] = self::HTTP_SUCCESS;
        $response['body'] = json_encode($result);
        return $response;
    }

    private function create(): array
    {
        if (!$this->user->hasPermission($this->module::PERMISSION_AREA, 'CREATE')) {
            return $this->notAuthorized();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->module->create($data);

        $response['status_code_header'] = self::HTTP_CREATED;
        $response['body'] = json_encode($result);
        return $response;
    }

    private function update(int $id): array
    {
        if (!$this->user->hasPermission($this->module::PERMISSION_AREA, 'UPDATE')) {
            return $this->notAuthorized();
        }

        if (!$this->module->getOne($id)) {
            // fight doesn't exist
            return $this->notFound();
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $this->module->update($id, $data);


        $response['status_code_header'] = self::HTTP_SUCCESS_NO_CONTENT;
        return $response;
    }

    private function delete(int $id): array
    {
        if (!$this->user->hasPermission($this->module::PERMISSION_AREA, 'DELETE')) {
            return $this->notAuthorized();
        }

        if (!$this->module->getOne($id)) {
            return $this->notFound();
        }

        $this->module->delete($id);
        $response['status_code_header'] = self::HTTP_SUCCESS_NO_CONTENT;

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