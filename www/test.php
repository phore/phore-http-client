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

    case "400":
        header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request", true, 400);
        echo "ABC";
        exit;

    case "500":
        header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error", true, 500);
        echo "ABC";
        exit;

    case "wait":
        $id = uniqid();
        for($i = 0; $i<10; $i++) {
            echo "Line.$id\n";
            sleep(1);
        }
        exit;

    case "redir_unlimited":
        header("Location: test.php?case=redir_unlimited");
        exit;

    case "stream":
        //ob_implicit_flush(true);
        for($i=0; $i<1000; $i++) {
            echo "\n$i" . str_pad("A", 100, "A");
        }
        exit;

    case "dump":
        echo json_encode($_SERVER);
        exit;

    case "upload":
        //ob_implicit_flush(true);
        echo file_get_contents("php://input");
        exit;
}
