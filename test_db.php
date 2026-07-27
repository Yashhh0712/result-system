<?php
header('Content-Type: text/plain');
echo "=== TiDB Connection Test ===\n\n";

$host = getenv('DB_HOST') ?: 'NOT SET';
$dbname = getenv('DB_NAME') ?: 'NOT SET';
$username = getenv('DB_USER') ?: 'NOT SET';
$port = getenv('DB_PORT') ?: 'NOT SET';
$dbpass = getenv('DB_PASS') ?: '';

echo "DB_HOST: $host\n";
echo "DB_NAME: $dbname\n";
echo "DB_USER: $username\n";
echo "DB_PORT: $port\n";
echo "DB_PASS: " . (strlen($dbpass) > 0 ? '[SET len=' . strlen($dbpass) . ']' : '[EMPTY]') . "\n";
echo "PHP: " . phpversion() . "\n";
echo "mysqli: " . (extension_loaded('mysqli') ? 'YES' : 'NO') . "\n";

$mysqli = new mysqli();
echo "\nAttempting connection...\n";

$ok = @$mysqli->real_connect($host, $username, $dbpass, $dbname, (int)$port, MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);

if ($mysqli->connect_error) {
    echo "SSL+NOVERIFY failed: " . $mysqli->connect_error . " (" . $mysqli->connect_errno . ")\n";

    $mysqli2 = new mysqli();
    $ok2 = @$mysqli2->real_connect($host, $username, $dbpass, $dbname, (int)$port, MYSQLI_CLIENT_SSL);
    if ($mysqli2->connect_error) {
        echo "SSL only failed: " . $mysqli2->connect_error . " (" . $mysqli2->connect_errno . ")\n";
    } else {
        echo "SSL only succeeded!\n";
        $mysqli2->close();
    }

    $mysqli3 = new mysqli();
    $ok3 = @$mysqli3->real_connect($host, $username, $dbpass, $dbname, (int)$port);
    if ($mysqli3->connect_error) {
        echo "No SSL failed: " . $mysqli3->connect_error . " (" . $mysqli3->connect_errno . ")\n";
    } else {
        echo "No SSL succeeded!\n";
        $mysqli3->close();
    }
} else {
    echo "Connected successfully with SSL!\n";
    echo "Server: " . $mysqli->server_info . "\n";
    $result = $mysqli->query("SELECT 1 AS test");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Query test: " . json_encode($row) . "\n";
    }
    $mysqli->close();
}
?>
