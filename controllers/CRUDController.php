<?php

namespace controllers;

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

    /**
     * CRUDController constructor.
     * @param object $module an instantiated object of a model class
     * @param User $user an instantiated user object
     * @param string $requestMethod the HTTP request type
     * @param int|null $moduleId the unique identifier for the request (if applicable)
     * @param array|null $queryStrings the url query strings (if applicable)
     */
    public function __construct(object $module, User $user, string $requestMethod, ?int $moduleId, ?array $queryStrings)
    {
        $this->requestMethod = $requestMethod;
        $this->moduleId = $moduleId;
        $this->user = $user;
        $this->module = $module;
        $this->queryStrings = $queryStrings;
    }

    /**
     * Routes all HTTP requests to the appropriate functions - this must be called after the object is created.
     */
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

    /**
     * Return a list of module data for displaying. Pagination is included, limiting the maximum results per request to
     * that set in the constant MAX_RECORDS.
     *
     * The permissions for the user making the request will be checked prior to execution.
     *
     * @return array returns list of data related to the module
     */
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

        $response['body']['totalResults'] = $this->module->getTotal();
        $response['body']['currentResults'] = sizeof($result);
        $response['body']['links'] = $this->createLinks($start, $limit, sizeof($result));
        $response['body']['data'] = $result;
        return $response;
    }

    /**
     * Returns detailed data for one specific module item.
     *
     * The permissions for the user making the request will be checked prior to execution.
     *
     * @param int $id
     * @return array
     */
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

    /**
     * Handles the creation of a new module object. This works by fetching the POST data via php://input and hence no
     * data parameter is required.
     *
     * The permissions for the user making the request will be checked prior to execution.
     *
     * @return array information on the newly created object
     */
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

    /**
     * Handles the updating of an existing module item. This works by fetching the POST data via php://input and hence no
     * data parameter is required.
     *
     * The permissions for the user making the request will be checked prior to execution.
     *
     * @param int $id
     * @return array
     */
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

    /**
     * Handles the deletion of a module item.
     * The permissions for the user making the request will be checked prior to execution.
     *
     * @param int $id
     * @return array
     */
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

    /**
     * Creates a list of links for paginated results which will output:
     *  self - current request url
     *  next - if there are more results, will display the url to view them
     *  prev - if there are any previous results, will display the url to view them
     *
     * @param int $start the record that browsing started at
     * @param int $limit the maximum records that will be returned to the user as part of the request
     * @param int $resultSize the total number of records retrieved and returned to the user
     * @return array containing links [self, next, prev]
     */
    private function createLinks(int $start, int $limit, int $resultSize): array
    {
        $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $nextQuery['start'] = $start + $limit;
        $nextQuery['limit'] = $limit;
        $nextQuery['apiKey'] = $this->queryStrings['apiKey'] ?? null;
        $next = $urlPath . "?" . http_build_query($nextQuery);

        $prevStart = $start - $limit;

        $prevQuery['start'] = ($prevStart < 0 ? 0 : $prevStart);
        $prevQuery['limit'] = $limit;
        $prevQuery['apiKey'] = $this->queryStrings['apiKey'] ?? null;
        $prev = $urlPath . "?" . http_build_query($prevQuery);

        return [
            "self" => $_SERVER['REQUEST_URI'],
            "next" => ($resultSize < $limit ? "" : $next),
            "prev" => ($start == 0 ? "" : $prev)
        ];
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