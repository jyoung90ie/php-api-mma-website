<?php

namespace controllers;

use PDO;
use models\User;
use models\Fight;


class FightController
{
    const HTTP_SUCCESS = 'HTTP/1.1 200 OK';
    const HTTP_CREATED = 'HTTP/1.1 201 Created';
    const HTTP_SUCCESS_NO_CONTENT = 'HTTP/1.1 204 No Content'; // update and delete
    const HTTP_UNAUTHORIZED = 'HTTP/1.1 401 Unauthorized';
    const HTTP_NOT_FOUND = 'HTTP/1.1 404 Not Found';
    const HTTP_FORBIDDEN = 'HTTP/1.1 403 Forbidden';
    const PERMISSION_AREA = 'FIGHTS';

    private string $request;
    private User $user;
    private ?int $fightId;
    private Fight $fight;

    public function __construct(PDO $db, string $request, ?int $fightId, User $user)
    {
        $this->request = $request;
        $this->fightId = $fightId;
        $this->user = $user;

        $this->fight = new Fight($db);
    }

    public function process_request()
    {
        switch ($this->request) {
            case 'POST':
                // create
                $response = $this->create();
                break;
            case 'GET':
                // read
                if (!is_null($this->fightId)) {
                    $response = $this->getOne($this->fightId);
                } else {
                    $response = $this->getAll();
                }
                break;
            case 'PUT':
                // update
                $response = $this->update($this->fightId);
                break;
            case 'DELETE':
                // delete
                $response = $this->delete($this->fightId);
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
        if (!$this->user->hasPermission(self::PERMISSION_AREA, 'READ')) {
            return $this->notAuthorized();
        }

        $result = $this->fight->getAll();
        $response['status_code_header'] = self::HTTP_SUCCESS;
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getOne(int $id): array
    {
        if (!$this->user->hasPermission(self::PERMISSION_AREA, 'READ')) {
            return $this->notAuthorized();
        }

        $result = $this->fight->getOne($id);
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
        if (!$this->user->hasPermission(self::PERMISSION_AREA, 'CREATE')) {
            return $this->notAuthorized();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->fight->create($data);

        $response['status_code_header'] = self::HTTP_CREATED;
        $response['body'] = json_encode($result);
        return $response;
    }

    private function update(int $id): array
    {
        if (!$this->user->hasPermission(self::PERMISSION_AREA, 'UPDATE')) {
            return $this->notAuthorized();
        }

        if (!$this->fight->getOne($id)) {
            // fight doesn't exist
            return $this->notFound();
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $this->fight->update($id, $data);


        $response['status_code_header'] = self::HTTP_SUCCESS_NO_CONTENT;
        return $response;
    }

    private function delete(int $id): array
    {
        if (!$this->user->hasPermission(self::PERMISSION_AREA, 'DELETE')) {
            return $this->notAuthorized();
        }

        if (!$this->fight->getOne($id)) {
            // fight doesn't exist
            return $this->notFound();
        }

        $this->fight->delete($id);
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