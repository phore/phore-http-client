<?php

require dirname(__DIR__) . "/vendor/autoload.php";

$socket = stream_socket_server("tcp://127.0.0.1:0", $errorCode, $errorMessage);
if ($socket === false) {
    throw new RuntimeException("Unable to allocate test server port: " . $errorMessage);
}
$address = stream_socket_get_name($socket, false);
fclose($socket);

$serverLog = tempnam(sys_get_temp_dir(), "phore-http-test-");
if ($serverLog === false) {
    throw new RuntimeException("Unable to create test server log.");
}

$serverProcess = proc_open(
    [PHP_BINARY, "-S", $address, "-t", dirname(__DIR__) . "/www"],
    [
        0 => ["pipe", "r"],
        1 => ["file", $serverLog, "a"],
        2 => ["file", $serverLog, "a"]
    ],
    $pipes,
    dirname(__DIR__)
);

if ( ! is_resource($serverProcess)) {
    unlink($serverLog);
    throw new RuntimeException("Unable to start test server.");
}
fclose($pipes[0]);

register_shutdown_function(function () use ($serverProcess, $serverLog) {
    proc_terminate($serverProcess);
    proc_close($serverProcess);
    unlink($serverLog);
});

define("TEST_BASE_URL", "http://" . $address);
$context = stream_context_create(["http" => ["timeout" => 1]]);
for ($i = 0; $i < 50; $i++) {
    if ( ! proc_get_status($serverProcess)["running"]) {
        break;
    }
    if (@file_get_contents(TEST_BASE_URL . "/test.php?case=200", false, $context) === "ABC") {
        return;
    }
    usleep(100000);
}

throw new RuntimeException("Test server did not become ready:\n" . file_get_contents($serverLog));
