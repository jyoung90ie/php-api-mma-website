<?php
require_once 'header.php';
?>
    <main class="events-container container-fluid">
    <ul class="h2 text-center list-inline header-font">
        <li class="list-inline-item active">Upcoming</li>
        <li class="list-inline-item">Past</li>
    </ul>
    <!-- Event -->
    <div class="event row">
        <div class="col-md-2 text-md-center">
            <span class="header-font">Pro MMA <br>123</span>
        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-4">
                    <img src="../images/fight1.jpg" alt="...">
                </div>
                <div class="offset-1 col">
                    <span class="h5 header-font mb-2">FIGHTER 1 vs FIGHTER 2</span>
                    <span>3 April 2021, 9.00pm GMT</span>
                    <span>O2 Arena</span>
                    <span>London, England</span>
                </div>
            </div>
        </div>
    </div>
    <!-- ./Event -->
    <!-- Event -->
    <div class="event row">
        <div class="col-md-2 text-md-center">
            <span class="header-font">Pro MMA <br>124</span>
        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-4">
                    <img src="../images/fight2.jpg" alt="...">
                </div>
                <div class="offset-1 col">
                    <span class="h5 header-font mb-2">FIGHTER 3 vs FIGHTER 4</span>
                    <span>15 May 2021, 9.30pm GMT</span>
                    <span>3 Arena</span>
                    <span>Dublin, Ireland</span>
                </div>
            </div>
        </div>
    </div>
    <!-- ./Event -->
    <!-- Event -->
    <div class="event row">
        <div class="col-md-2 text-md-center">
            <span class="header-font">Pro MMA <br>125</span>
        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-4">
                    <img src="../images/fight1.jpg" alt="...">
                </div>
                <div class="offset-1 col">
                    <span class="h5 header-font mb-2">FIGHTER 5 vs FIGHTER 6</span>
                    <span>14 June 2021, 9.30pm GMT</span>
                    <span>Odyssey Arena</span>
                    <span>Belfast, N. Ireland</span>
                </div>
            </div>
        </div>
    </div>
    <!-- ./Event -->
    <!-- Event -->
    <div class="event row">
        <div class="col-md-2 text-md-center">
            <span class="header-font">Pro MMA <br>126</span>
        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-4">
                    <img src="../images/fight2.jpg" alt="...">
                </div>
                <div class="offset-1 col">
                    <span class="h5 header-font mb-2">FIGHTER 7 vs FIGHTER 8</span>
                    <span>23 July 2021, 9.00pm GMT</span>
                    <span>3 Arena</span>
                    <span>Dublin, Ireland</span>
                </div>
            </div>
        </div>
    </div>
    <!-- ./Event -->

    <!-- Pagination -->
    <nav aria-label="Events page naviation">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item disabled"><a class="page-link" href="#">2</a></li>
            <li class="page-item disabled"><a class="page-link" href="#">3</a></li>
            <li class="page-item disabled">
                <a class="page-link" href="#">Next</a>
            </li>
        </ul>
    </nav>
    <!-- ./Pagination -->
    </main>

<?php
require_once 'footer.php';
?>