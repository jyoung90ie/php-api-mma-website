<?php

$apiEndPoint = API_URL . '/user?apiKey=' . API_KEY;

if (isset($_SESSION['User'])) {
    header("Location: ?page=index");
}

if (!constant("API_URL")) {
    echo 'Api address not set';
    return;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = [
        'UserName', 'UserPassword', 'UserPasswordConfirm', 'UserEmail', 'UserFirstName', 'UserLastName', 'UserDOB'
    ];
    $validationErrors = [];

    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $validationErrors[$field] = 'Field ' . $field . ' must be populated';
        }
    }

    // passwords must match
    if (isset($_POST['UserPassword']) && isset($_POST['UserPasswordConfirm'])
        && ($_POST['UserPassword'] != $_POST['UserPasswordConfirm'])) {
        $output = '<br />Passwords do not match';
        if (isset($validationErrors['UserPassword'])) {
            $validationErrors['UserPassword'] .= $output;
        } else {
            $validationErrors['UserPassword'] = $output;
        }
    }


    // api processing
    if (sizeof($validationErrors) == 0) {
        // convert form data into array
        $formContents = file_get_contents("php://input");
        parse_str($formContents, $formContents);
        // add default role
        $formContents['RoleID'] = 2;
        $jsonContents = json_encode($formContents);

        $apiRequest = curl_init();
        curl_setopt($apiRequest, CURLOPT_URL, $apiEndPoint);
        curl_setopt($apiRequest, CURLOPT_POST, true);
        curl_setopt($apiRequest, CURLOPT_POSTFIELDS, $jsonContents);
        curl_setopt($apiRequest, CURLOPT_RETURNTRANSFER, true);

        $apiResponse = json_decode(curl_exec($apiRequest), true);
        $metaResponse = curl_getinfo($apiRequest);

        // if created, header will return 201
        if (isset($metaResponse['http_code']) && $metaResponse['http_code'] == 201) {

            unset($apiResponse); // api will return number of records created when successfully

            $userNotification = 'Account created, you can now login with the username: <strong>' . $_POST['UserName']
                . '</strong>';
            \helpers\HelperFunctions::addNotification($userNotification);

            header("Location: ?page=index");
        }
    }
}

?>

    <main class="container">
        <h2>Register</h2>

        <?= \helpers\HelperFunctions::displayApiError($apiResponse ?? []); ?>
        <form action="" method="post">

            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="UserName" class="col-form-label">Username</label>
                </div>
                <div class="col-auto">
                    <input type="text" name="UserName" class="form-control"
                           value="<?= $_POST['UserName'] ?? '' ?>" required>
                </div>
                <div class="col-auto">
                    <span id="UserNameErrors" class="form-text error">
                    <?= $validationErrors['UserName'] ?? '' ?>
                    </span>
                </div>
            </div>
            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="UserPassword" class="col-form-label">Password</label>
                </div>
                <div class="col-auto">
                    <input type="password" name="UserPassword" class="form-control"
                           value="<?= $_POST['UserPassword'] ?? '' ?>" required>
                </div>
                <div class="col-auto">
                    <span id="UserPasswordErrors" class="form-text error">
                    <?= $validationErrors['UserPassword'] ?? '' ?>
                    </span>
                </div>
            </div>
            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="UserPasswordConfirm" class="col-form-label">Confirm Password</label>
                </div>
                <div class="col-auto">
                    <input type="password" name="UserPasswordConfirm" class="form-control"
                           value="<?= $_POST['UserPasswordConfirm'] ?? '' ?>" required>
                </div>
                <div class="col-auto">
                    <span id="UserPasswordConfirmErrors" class="form-text error">
                    <?= $validationErrors['UserPasswordConfirm'] ?? '' ?>
                    </span>
                </div>
            </div>
            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="UserEmail" class="col-form-label">Email Address</label>
                </div>
                <div class="col-auto">
                    <input type="email" name="UserEmail" class="form-control"
                           value="<?= $_POST['UserEmail'] ?? '' ?>" required>
                </div>
                <div class="col-auto">
                    <span id="UserEmailErrors" class="form-text error">
                    <?= $validationErrors['UserEmail'] ?? '' ?>
                    </span>
                </div>
            </div>
            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="UserFirstName" class="col-form-label">First Name</label>
                </div>
                <div class="col-auto">
                    <input type="text" name="UserFirstName" class="form-control"
                           value="<?= $_POST['UserFirstName'] ?? '' ?>" required>
                </div>
                <div class="col-auto">
                    <span id="UserFirstNameErrors" class="form-text error">
                    <?= $validationErrors['UserFirstName'] ?? '' ?>
                    </span>
                </div>
            </div>
            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="UserLastName" class="col-form-label">Last Name</label>
                </div>
                <div class="col-auto">
                    <input type="text" name="UserLastName" class="form-control"
                           value="<?= $_POST['UserLastName'] ?? '' ?>" required>
                </div>
                <div class="col-auto">
                    <span id="UserLastNameErrors" class="form-text error">
                    <?= $validationErrors['UserLastName'] ?? '' ?>
                    </span>
                </div>
            </div>
            <div class="row g-3 align-items-center mb-3">
                <div class="col-2">
                    <label for="UserDOB" class="col-form-label">Date of Birth</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="UserDOB" class="form-control"
                           value="<?= $_POST['UserDOB'] ?? '' ?>" required>
                </div>
                <div class="col-auto">
                    <span id="UserDOBErrors" class="form-text error">
                    <?= $validationErrors['UserDOB'] ?? '' ?>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>


    </main>
<?php
