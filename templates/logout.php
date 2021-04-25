<?php
if (isset($_SESSION['UserID'])) {
    unset($_SESSION['UserID'], $_SESSION['User']);
}

header("Location: ?page=index");
