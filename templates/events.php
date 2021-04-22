<?php

require_once '../autoload.php';

use helpers\APIRequest;

require_once 'header.php';

$apiAddress = "http://localhost:8888/promma/api";
$apiModule = "/event";

$queryString['start'] = intval($_GET['start'] ?? 0);

$apiRequest = new APIRequest($apiAddress, $apiModule, null, $queryString);
$results = $apiRequest->fetchApiData();

$events = $results['data'];
?>
    <main class="events-container container">
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
        <nav aria-label="Events page navigation">
            <ul class="pagination justify-content-center">
                <?= $apiRequest->displayPagination(); ?>
            </ul>
        </nav>
        <!-- ./Pagination -->
    </main>

<?php
require_once 'footer.php';