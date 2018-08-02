<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 02.08.18
 * Time: 11:55
 */


switch ($_GET["case"]) {
    case "200":
        header("Content-Type: text/plain");
        echo "ABC";
        exit;

    case "300":
        header("Location: test.php?case=200");
        exit;

    case "500":
        header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error", true, 500);
        echo "ABC";
        exit;

    case "stream":
        //ob_implicit_flush(true);
        for($i=0; $i<1000000; $i++) {
            echo "\n$i" . str_pad("A", 100, "A");
        }
        exit;
}
