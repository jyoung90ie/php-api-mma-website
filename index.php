<?php

require("config/Database.php");

if (isset($db)) {

    $result = $db->query("SELECT * FROM Athletes");

    $longestName = "";
    $longestNameLength = 0;


    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $name = $row['AthleteName'];
            $nameLength = strlen($name);

            if ($nameLength > $longestNameLength) {
                $longestNameLength = $nameLength;
                $longestName = $name;
            }
        }

        echo "Longest name: " . $longestName . "\n";
        echo "Length: " . $longestNameLength;

        $db->close();
    }
} else {
    echo "No DB results";
}


?>