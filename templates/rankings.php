<?php
require_once 'header.php';
?>
    <main class="rankings-container container">
    <h2>Top 15 athletes by weight division</h2>
    <ul class="nav nav-pills" id="athleteRankings" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="fly-tab" data-toggle="tab" href="#fly" role="tab"
               aria-selected="true">Fly</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="bantam-tab" data-toggle="tab" href="#bantam" role="tab">Bantam</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="feather-tab" data-toggle="tab" href="#feather" role="tab">Feather</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" id="light-tab" data-toggle="tab" href="#light" role="tab">Light</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" id="welter-tab" data-toggle="tab" href="#welter" role="tab">Welter</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" id="middle-tab" data-toggle="tab" href="#middle" role="tab">Middle</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" id="lightheavy-tab" data-toggle="tab" href="#lightheavy" role="tab">Light
                Heavy</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" id="heavy-tab" data-toggle="tab" href="#heavy" role="tab">Heavy</a>
        </li>
    </ul>
    <div class="tab-content" id="athleteRankingsContent">
        <!-- Fly -->
        <div class="tab-pane fade show active" id="fly" role="tabpanel" aria-labelledby="fly-tab">
            <table class="table table-responsive table-hover">
                <thead>
                <tr class="table-dark">
                    <th scope="col">Rank</th>
                    <th scope="col">Athlete</th>
                    <th scope="col">Last 5 fights</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Champion</td>
                    <td>Deiveson Figueiredo</td>
                    <td>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Brandon Moreno</td>
                    <td>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Joseph Benavidez</td>
                    <td>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-secondary">D</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Askar Askarov</td>
                    <td>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>...</td>
                    <td>...</td>
                    <td>...</td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- ./Fly -->
        <!-- Bantam -->
        <div class="tab-pane fade" id="bantam" role="tabpanel" aria-labelledby="bantam-tab">
            <table class="table table-responsive table-hover">
                <thead>
                <tr class="table-dark">
                    <th scope="col">Rank</th>
                    <th scope="col">Athlete</th>
                    <th scope="col">Last 5 fights</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Champion</td>
                    <td>Aljamain Sterling</td>
                    <td>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Petr Yan</td>
                    <td>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Cory Sandhagen</td>
                    <td>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Rob Font</td>
                    <td>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-secondary">D</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>...</td>
                    <td>...</td>
                    <td>...</td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- ./Bantam -->
        <!-- Feather -->
        <div class="tab-pane fade" id="feather" role="tabpanel" aria-labelledby="feather-tab">
            <table class="table table-responsive table-hover">
                <thead>
                <tr class="table-dark">
                    <th scope="col">Rank</th>
                    <th scope="col">Athlete</th>
                    <th scope="col">Last 5 fights</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Champion</td>
                    <td>Alexander Volkanovski</td>
                    <td>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Max Holloway</td>
                    <td>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-danger">L</span>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Brian Ortega</td>
                    <td>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-danger">L</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Zabit Magomedsharipov</td>
                    <td>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-success">W</span>
                        <span class="badge bg-secondary">D</span>
                        <span class="badge bg-success">W</span>
                    </td>
                </tr>
                <tr>
                    <td>...</td>
                    <td>...</td>
                    <td>...</td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- ./Feather -->
    </div>
    </main>


<?php
require_once 'footer.php';
?>