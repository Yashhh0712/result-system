<?php
header('Content-Type: text/plain');
echo "=== TiDB Connection Test ===\n\n";

echo "PHP Version: " . phpversion() . "\n";
echo "Extensions: " . implode(', ', get_loaded_extensions()) . "\n\n";

$host = getenv('DB_HOST') ?: 'NOT SET';
$dbname = getenv('DB_NAME') ?: 'NOT SET';
$username = getenv('DB_USER') ?: 'NOT SET';
$port = getenv('DB_PORT') ?: 'NOT SET';

echo "DB_HOST: $host\n";
echo "DB_NAME: $dbname\n";
echo "DB_USER: $username\n";
echo "DB_PORT: $port\n";
echo "DB_PASS: " . (getenv('DB_PASS') ? '[SET]' : '[NOT SET]') . "\n\n";

$hasMysqli = extension_loaded('mysqli');
echo "mysqli extension: " . ($hasMysqli ? 'YES' : 'NO') . "\n";
echo "PDO extension: " . (extension_loaded('pdo') ? 'YES' : 'NO') . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n\n";

$isExternal = !in_array($host, ['localhost', '127.0.0.1', '::1', 'NOT SET']);
echo "Is external: " . ($isExternal ? 'YES' : 'NO') . "\n\n";

if ($isExternal) {
    $dbpass = getenv('DB_PASS') ?: '';
    
    echo "Attempting mysqli connection with SSL...\n";
    $mysqli = @new mysqli();
    $mysqli->ssl_set(null, null, '/etc/ssl/certs/ca-certificates.crt', null, null);
    $connected = $mysqli->real_connect($host, $username, $dbpass, $dbname, (int)$port, MYSQLI_CLIENT_SSL);
    
    if ($mysqli->connect_error) {
        echo "mysqli FAILED: " . $mysqli->connect_error . "\n";
        echo "errno: " . $mysqli->connect_errno . "\n";
        
        echo "\nTrying without explicit SSL flag...\n";
        $mysqli2 = @new mysqli();
        $connected2 = @$mysqli2->real_connect($host, $username, $dbpass, $dbname, (int)$port);
        if ($mysqli2->connect_error) {
            echo "Also failed: " . $mysqli2->connect_error . "\n";
        } else {
            echo "Connected without SSL flag!\n";
        }
    } else {
        echo "mysqli CONNECTED successfully with SSL!\n";
        echo "Server version: " . $mysqli->server_info . "\n";
        $mysqli->close();
    }
} else {
    echo "No external DB configured, skipping test.\n";
}
?>
