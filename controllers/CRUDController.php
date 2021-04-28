<?php

namespace controllers;

use Exception;
use models\Athlete;
use models\FightAthlete;
use models\Search;
use models\User;
use PDOException;
use TypeError;


class CRUDController
{
    const HTTP_SUCCESS = 'HTTP/1.1 200 OK';
    const HTTP_CREATED = 'HTTP/1.1 201 Created';
    const HTTP_SUCCESS_NO_CONTENT = 'HTTP/1.1 204 No Content'; // update and delete
    const HTTP_BAD_REQUEST = 'HTTP/1.1 400 Bad Request';
    const HTTP_UNAUTHORIZED = 'HTTP/1.1 401 Unauthorized';
    const HTTP_FORBIDDEN = 'HTTP/1.1 403 Forbidden';
    const HTTP_NOT_FOUND = 'HTTP/1.1 404 Not Found';

    const MAX_RECORDS = 5; // maximum number of records that can be return per api request

    private $moduleId;
    private $user;
    private $requestMethod;
    private $module;
    private $queryStrings;

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

        try {
            switch ($this->requestMethod) {
                case 'POST':
                    // create
                    $response = $this->handlePost();
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
        } catch (PDOException | Exception | TypeError $exception) {
            exit(json_encode(['Error' => $exception->getMessage()]));
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

            // if systemOverride is not set, then apply default limitations
            if (!isset($this->queryStrings['limitOverride'])) {
                $limit = ($limit < self::MAX_RECORDS ? $limit : self::MAX_RECORDS);
            }
        }

        if (isset($this->queryStrings['start'])) {
            $start = $this->queryStrings['start'];
            $start = ($start > 0 ? $start : 0);
        }

        if (isset($this->queryStrings['random']) && $this->module instanceof Athlete) {
            $result = $this->module->getRandom(); // returns 3 random athletes
        } else {
            $result = $this->module->getAll($limit, $start);
        }


        $currentResults = 0;
        $data = [];

        // make sure there are results first
        if ($result) {
            $currentResults = sizeof($result);
            $data = $result;
        }

        $response['status_code_header'] = self::HTTP_SUCCESS;

        $response['body']['totalResults'] = $this->module->getTotal();
        $response['body']['resultsPerPage'] = $limit;
        $response['body']['currentResults'] = $currentResults;
        $response['body']['links'] = $this->createLinks($start, $limit, sizeof($result));
        $response['body']['data'] = $data;
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

        if ($this->module instanceof FightAthlete) {
            $result = $this->module->getByFightId($id);
        } else {
            $result = $this->module->getOne($id);
        }

        if (!$result) {
            // fight doesn't exist
            return $this->notFound();
        }
        $response['status_code_header'] = self::HTTP_SUCCESS;
        $response['body'] = $result;
        return $response;
    }

    /**
     * Determines whether a post request should be handled by create() or by search()
     * @return array
     */
    private function handlePost(): array
    {
        if ($this->module instanceof Search) {
            return $this->search();
        } else {
            return $this->create();
        }
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

        if (!$result) {
            return $this->badRequest();
        }

        $response['status_code_header'] = self::HTTP_CREATED;
        $response['body'] = $result;
        return $response;

    }


    private function search(): array
    {
        $searchTerm = json_decode(file_get_contents('php://input'), true);

        $result = $this->module->searchByAthleteName($searchTerm);

        if (!$result) {
            return $this->badRequest();
        }

        $response['status_code_header'] = self::HTTP_SUCCESS;
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
            // item doesn't exist
            return $this->notFound();
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // if an update query is run but no fields are changed, sql will return 0 rows affected
        $response['body'] = $this->module->update($id, $data);
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

        $self = $urlPath . "?" . http_build_query(['start' => $start, 'limit' => $limit]);

        return [
            "self" => $self,
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
        $response['body'] = ['Error' => 'Record with specified ID does not exist.'];
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
        $response['body'] = ['Error' => 'You need to be logged in to complete this action.'];

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
        $response['body'] = ['Error' => 'You do not have permission to access this.'];

        return $response;
    }

    /**
     * Used when a user tries to make a request that failed.
     *
     * @return array containing the appropriate HTTP response header
     */
    private function badRequest(): array
    {
        $response['status_code_header'] = self::HTTP_BAD_REQUEST;
        $response['body'] = ['Error' => 'There was a problem with the  request - check the data.'];
        return $response;
    }

}