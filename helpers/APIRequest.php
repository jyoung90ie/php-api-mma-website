<?php


namespace helpers;


class APIRequest
{
    private $apiBaseUrl;
    private $apiModule;
    private $itemId;
    private $queryStrings;
    private $apiResponse = null;
    private $apiKey;

    public function __construct(string $url, string $module, ?string $apiKey, ?int $itemId = null, ?array $queryStrings = null)
    {
        $this->apiBaseUrl = rtrim($url, '/');
        $this->apiModule = trim($module, '/');
        $this->itemId = intvaL(ltrim($itemId, '/'));
        $this->queryStrings = $queryStrings;
        $this->apiKey = $apiKey;
    }

    /**
     * Retrieve data from api end point and store in instance var.
     */
    public function fetchApiData()
    {
        $apiDataUrl = $this->apiBaseUrl . '/' . $this->apiModule;

        if (isset($this->itemId)) {
            $apiDataUrl .= '/' . $this->itemId;
        }

        $apiDataUrl .= '?apiKey=' . $this->apiKey;
        if (isset($this->queryStrings)) {
            // remove any apiKey already set as part of querystring
            if (isset($this->queryStrings['apiKey'])) {
                unset($this->queryStrings['apiKey']);
            }

            $apiDataUrl .= '&' . http_build_query($this->queryStrings);
        }

        $apiRequest = curl_init();
        curl_setopt($apiRequest, CURLOPT_URL, $apiDataUrl);
        curl_setopt($apiRequest, CURLOPT_RETURNTRANSFER, true);

        $apiResponse = curl_exec($apiRequest);
        $metaResponse = curl_getinfo($apiRequest);

        $httpCode = $metaResponse['http_code'];
        $response = [];

        if ($httpCode == 200 || $httpCode == 201 || $httpCode == 204) {
            // successful GET (200) or successful POST (201)
            $response = json_decode($apiResponse, true);
        } else if ($httpCode == 400) {
            // not authenticated
            $response['Error'] = 'Bad request';

        } else if ($httpCode == 401) {
            // not authenticated
            $response['Error'] = 'Not authenticated';
        } else if ($httpCode == 403) {
            // no permission to access
            $response['Error'] = 'No permission';
        } else if ($httpCode == 404) {
            // not found
            if (isset($this->queryStrings)) {
                // try again without query strings
                $this->queryStrings = null;
                return $this->fetchApiData();
            }
            // definitely doesn't exist
            $response['Error'] = 'Page not found';

        } else {
            // something unexpected
            $response['Error'] = 'Unexpected error';
        }

        curl_close($apiRequest);

        $this->apiResponse = $response;
        return $this->apiResponse;
    }


    /**
     * Generates HTML for paginated results so that only a limited amount of information is shown on each page. This
     * also limits query impact.
     *
     * @return false|string HTML string if result is paginated, otherwise will return false.
     */
    function displayPagination()
    {
        $maxPaginationPages = 5;

        if (!isset($this->apiResponse) || !isset($this->apiResponse['links'])) {
            return false;
        }

        $existingQueryString = $this->queryStrings;
        unset($existingQueryString['start']);

        $links = $this->apiResponse['links'];
        parse_str(parse_url($links['self'], PHP_URL_QUERY), $currentPageResults);
        $totalResults = $this->apiResponse['totalResults'] ?? null;

        $startingResult = intval($currentPageResults['start']);
        $resultsPerPage = $this->apiResponse['resultsPerPage'] ?? null;

        $totalPages = intval(ceil($totalResults / $resultsPerPage) ?? 1);
        $activePage = intval(floor($startingResult / $resultsPerPage) + 1);


        $outputHTML = '';
        $prevLink = '';
        $prevLinkClass = ' disabled';
        $nextLink = '';
        $nextLinkClass = ' disabled';


        // previous button
        if ($activePage > 1) {
            $prevLink = '?' . http_build_query(array_merge($existingQueryString, ['start' => $startingResult - $resultsPerPage]));
            $prevLinkClass = '';
        }


        $pageDifferential = intval($maxPaginationPages / 2);

        $firstPage = $activePage - $pageDifferential;
        $firstPage = ($firstPage < 1 ? 1 : $firstPage);

        $lastPage = $activePage + $pageDifferential;
        $lastPage = ($lastPage > $totalPages ? $totalPages : $lastPage);

        $outputHTML .= '              <li class="page-item' . $prevLinkClass . '">
                        <a class="page-link" href="' . $prevLink . '">Previous</a>
                    </li>';

        for ($page = $firstPage; $page <= $lastPage; $page++) {
            $urlStartValue = $startingResult + $resultsPerPage * ($page - $activePage);
            $pageUrl = '?' . http_build_query(array_merge($existingQueryString, ['start' => $urlStartValue]));

            $activePageClass = '';

            if ($activePage == $page) {
                $activePageClass = ' active';
            }

            $outputHTML .= '              <li class="page-item' . $activePageClass . '">
                        <a class="page-link" href="' . $pageUrl . '">' . $page . '</a>
                        </li>';
        }

        // next button
        if ($activePage < $totalPages) {
            $nextLink = '?' . http_build_query(array_merge($existingQueryString, ['start' => $startingResult + $resultsPerPage]));
            $nextLinkClass = '';
        }

        $outputHTML .= '              <li class="page-item ' . $nextLinkClass . '">
                        <a class="page-link" href="' . $nextLink . '">Next</a>
                    </li>';


        return $outputHTML;
    }

}