<?php


if (isset($_SESSION['User'])) {
    header("Location: ?page=index");
}

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}

$authEndpoint = 'http://localhost:8888/promma/api/auth';
//$authUrl = $_SERVER['HTTP_HOST'] . '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') . $authEndpoint;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_POST['username'])) {
        $validationErrors['Username'] = 'Username must be provided';
    }

    if (!isset($_POST['password'])) {
        $validationErrors['Password'] = 'Password must be provided';
    }

    if (!isset($validationErrors)) {
        $formContents = file_get_contents("php://input");
        parse_str($formContents, $formContents);
        $jsonContents = json_encode($formContents);

        $authRequest = curl_init();
        curl_setopt($authRequest, CURLOPT_URL, $authEndpoint);
        curl_setopt($authRequest, CURLOPT_POST, true);
        curl_setopt($authRequest, CURLOPT_POSTFIELDS, $jsonContents);
        curl_setopt($authRequest, CURLOPT_RETURNTRANSFER, true);

        $authResponse = json_decode(curl_exec($authRequest), true);
        $metaResponse = curl_getinfo($authRequest);

        // check for errors
        if (isset($authResponse['UserID'])) {
            unset($_SESSION['UserID'], $_SESSION['User']);
            $_SESSION['User'] = $authResponse;

            header("Location: ?page=index");
        }
    }
}

?>

    <main class="container">
        <h2>Login</h2>

        <?php
        if (isset($authResponse['Error'])) {
            ?>

            <div class="alert alert-danger" role="alert">
                <?= var_dump($authResponse) ?? '' ?>
            </div>
            <?php
        }
        ?>
        <form action="" method="post">
            <div class="row g-3 align-items-center mb-3">
                <div class="col-auto">
                    <label for="username" class="col-form-label">Username</label>
                </div>
                <div class="col-auto">
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="col-auto">
                    <span id="usernameErrors" class="form-text error">
                    <?= $validationErrors['Username'] ?? '' ?>
                    </span>
                </div>
            </div>
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="password" class="col-form-label">Password</label>
                </div>
                <div class="col-auto">
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-auto">
                    <span id="passwordErrors" class="form-text error">
                    <?= $validationErrors['Password'] ?? '' ?>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>


    </main>
<?php
