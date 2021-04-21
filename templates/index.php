<?php
require_once 'header.php';
?>
    <main class="content container-fluid">
    <!-- NewsContainer -->
    <div class="news container">
        <h2>Latest News</h2>
        <div class="row">
            <!-- News1 -->
            <div class="card mb-3 col-lg-5">
                <div class="row no-gutters">
                    <div class="col-md-4 my-card-img">
                        <img src="../images/news1.jpg" class="card-img" alt="...">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title">Fighter Retires</h5>
                            <p class="card-text">After wide speculation it has been confirmed that Fighter McFighter
                                has retired...</p>
                            <p class="card-text"><small class="text-muted">Last updated 6 hours ago</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./News1 -->
            <!-- News2 -->
            <div class="card mb-3 offset-lg-1 col-lg-5">
                <div class="row no-gutters">
                    <div class="col-md-4 my-card-img">
                        <img src="../images/news2.jpg" class="card-img" alt="...">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title">Pro MMA 125 Date Confirmed</h5>
                            <p class="card-text">
                                Today it has been confirmed that Fighter A will face Fighter B for the belt on
                                24 August 2021. <br /> <br />The main card is still shaping up with news expected in
                                the
                                coming
                                weeks.
                            </p>
                            <p class="card-text"><small class="text-muted">Last updated 2 days ago</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./News2 -->
        </div>
        <!-- ./Horizontal Card -->
    </div>
    <!-- ./NewsContainer -->

    <!-- EventsContainer -->
    <div class="home-events-container">
        <h2>Upcoming Events</h2>
        <div class="container">
            <!-- Event1 -->
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
            <!-- ./Event1 -->
            <!-- Event2 -->
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
            <!-- ./Event2 -->
            <div class="text-center">
                <a href="events.php" class="btn btn-more">See More</a>
            </div>
        </div>
    </div>
    <!-- ./EventsContainer -->
    </main>


<?php
require_once 'footer.php';
?>