<?php

namespace controllers;

use PDO;
use models\User;


class CRUDController
{
    const HTTP_SUCCESS = 'HTTP/1.1 200 OK';
    const HTTP_CREATED = 'HTTP/1.1 201 Created';
    const HTTP_SUCCESS_NO_CONTENT = 'HTTP/1.1 204 No Content'; // update and delete
    const HTTP_UNAUTHORIZED = 'HTTP/1.1 401 Unauthorized';
    const HTTP_NOT_FOUND = 'HTTP/1.1 404 Not Found';
    const HTTP_FORBIDDEN = 'HTTP/1.1 403 Forbidden';

    const MAX_RECORDS = 10; // maximum number of records that can be return per api request

    private ?int $moduleId;
    private User $user;
    private string $requestMethod;
    private object $module;
    private ?array $queryStrings;

    public function __construct(object $module, User $user, string $request, ?int $moduleId, ?array $queryStrings)
    {
        $this->requestMethod = $request;
        $this->moduleId = $moduleId;
        $this->user = $user;
        $this->module = $module;
        $this->queryStrings = $queryStrings;
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
            echo json_encode($response['body']);
        }
    }

    private function getAll(): array
    {
        if (!$this->user->hasPermission($this->module::PERMISSION_AREA, 'READ')) {
            return $this->notAuthorized();
        }

        $limit = self::MAX_RECORDS;
        $start = 0;

        // setup pagination
        if (isset($this->queryStrings['limit'])) {
            $limit = $this->queryStrings['limit'];
            $limit = ($limit < self::MAX_RECORDS ? $limit : self::MAX_RECORDS);
        }

        if (isset($this->queryStrings['start'])) {
            $start = $this->queryStrings['start'];
            $start = ($start > 0 ? $start : 0);
        }

        $result = $this->module->getAll($limit, $start);

        $response['status_code_header'] = self::HTTP_SUCCESS;

        $response['body']['results'] = sizeof($result);
        $response['body']['links'] = $this->createLinks($start, $limit, sizeof($result));
        $response['body']['data'] = $result;
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
        $response['body'] = $result;
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
        $response['body'] = $result;
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

    private function createLinks(int $start, int $limit, int $resultSize): array {
        $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $nextQuery['start'] = $start + $limit;
        $nextQuery['limit'] = $limit;
        $next = $urlPath . "?" . http_build_query($nextQuery);

        $prevStart = $start - $limit;

        $prevQuery['start'] = ($prevStart < 0 ? 0 : $prevStart);
        $prevQuery['limit'] = $limit;
        $prev = $urlPath . "?" . http_build_query($prevQuery);

        return [
            "self" => $_SERVER['REQUEST_URI'],
            "next" => ($resultSize < $limit ? "" : $next),
            "previous" => ($start == 0 ? "" : $prev)
        ];
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