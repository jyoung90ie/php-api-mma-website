<?php
require_once 'header.php';

$apiAddress = "http://localhost:8888/promma/api";

$apiEndPoint = "/event";
$queryString['apiKey'] = "test123";
$queryString['start'] = intval($_GET['start']) ?? 0;
$queryString['limit'] = intval($_GET['limit']) ?? 5;

$apiDataUrl = $apiAddress . $apiEndPoint . '?' . http_build_query($queryString);

$apiData = json_decode(file_get_contents($apiDataUrl), true);
$events = $apiData['data'];

// pagination data
$links = $apiData['links'];

?>
    <main class="events-container container-fluid">
        <ul class="h2 text-center list-inline header-font">
            <li class="list-inline-item active">Upcoming</li>
            <li class="list-inline-item">Past</li>
        </ul>

        <?php
        foreach ($events as $event) {

            ?>
            <!-- Event -->
            <div class="event row">
                <div class="col-md-2 text-md-center">
                    <span class="header-font">Pro MMA <br><?= $event['EventID'] ?></span>
                </div>
                <div class="col-md-10">
                    <div class="row">
                        <div class="col-4">
                            <img src="../images/fight<?= rand(1, 2) ?>.jpg" alt="...">
                        </div>
                        <div class="offset-1 col">
                            <span class="h5 header-font mb-2">FIGHTER 1 vs FIGHTER 2</span>
                            <span><?= $event['EventDate'] ?></span>
                            <span><?= $event['EventLocation'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./Event -->
            <?php
        }
        ?>

        <!-- Pagination -->
        <nav aria-label="Events page naviation">
            <ul class="pagination justify-content-center">
                <?php displayPagination($links) ?>>
            </ul>
        </nav>
        <!-- ./Pagination -->
    </main>

<?php
require_once 'footer.php';


function displayPagination(array $apiLinks)
{
    $next = ($apiLinks['next'] !== "");
    $prev = ($apiLinks['prev'] !== "");

    parse_str(parse_url($apiLinks['next'], PHP_URL_QUERY), $nextQueryStrings);
    parse_str(parse_url($apiLinks['prev'], PHP_URL_QUERY), $prevQueryStrings);

    unset($nextQueryStrings['apiKey']);
    unset($prevQueryStrings['apiKey']);

    $nextLink = "?" . http_build_query($nextQueryStrings);
    $prevLink = "?" . http_build_query($prevQueryStrings);

    $nextUrl = ($next ? $nextLink : "");
    $prevUrl = ($prev ? $prevLink : "");
    $nextClasses = ($next ? "" : " disabled");
    $prevClasses = ($prev ? "" : " disabled");


    echo '              <li class="page-item' . $prevClasses . '">
                        <a class="page-link" href="' . $prevUrl . '">Previous</a>
                    </li>';

    echo '        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item disabled"><a class="page-link" href="#">2</a></li>
                <li class="page-item disabled"><a class="page-link" href="#">3</a></li>';

    echo '              <li class="page-item' . $nextClasses . '">
                        <a class="page-link" href="' . $nextUrl . '">Next</a>
                    </li>';


}