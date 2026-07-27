<?php
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'result_system';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';

$isExternal = !in_array($host, ['localhost', '127.0.0.1', '::1', '']);

try {
    if ($isExternal) {
        $mysqli = @new mysqli();
        @$mysqli->ssl_set(null, null, '/etc/ssl/certs/ca-certificates.crt', null, null);
        $ok = @$mysqli->real_connect($host, $username, $password, $dbname, (int)$port, MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
        if (!$ok || $mysqli->connect_error) {
            $mysqli = @new mysqli();
            $ok = @$mysqli->real_connect($host, $username, $password, $dbname, (int)$port, MYSQLI_CLIENT_SSL);
        }
        if (!$ok || $mysqli->connect_error) {
            die("Connection failed: " . ($mysqli->connect_error ?: 'unknown error'));
        }
        $mysqli->set_charset('utf8mb4');
        $pdo = new MysqliWrapper($mysqli);
    } else {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
} catch(Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

class MysqliWrapper {
    public $mysqli;
    function __construct($mysqli) { $this->mysqli = $mysqli; }
    function prepare($sql) {
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) return false;
        return new MysqliStmtWrapper($stmt);
    }
    function exec($sql) {
        $this->mysqli->multi_query($sql);
        do { if ($r = $this->mysqli->store_result()) $r->free(); } while ($this->mysqli->more_results() && $this->mysqli->next_result());
        return $this->mysqli->affected_rows;
    }
    function lastInsertId() { return $this->mysqli->insert_id; }
}

class MysqliStmtWrapper {
    private $stmt;
    function __construct($stmt) { $this->stmt = $stmt; }
    function execute($params = []) {
        if (!empty($params)) {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p)) $types .= 'i';
                elseif (is_float($p)) $types .= 'd';
                else $types .= 's';
            }
            $this->stmt->bind_param($types, ...$params);
        }
        $this->stmt->execute();
        return true;
    }
    function fetch($mode = null) {
        $result = $this->stmt->get_result();
        if (!$result) return false;
        return $result->fetch_assoc();
    }
    function fetchAll($mode = null) {
        $result = $this->stmt->get_result();
        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    function rowCount() {
        $result = $this->stmt->get_result();
        return $result ? $result->num_rows : $this->stmt->affected_rows;
    }
}

session_start();

function isLoggedIn() { return isset($_SESSION['user_id']); }
function getUserRole() { return $_SESSION['role'] ?? null; }
function getStudentId() { return $_SESSION['student_id'] ?? null; }
function redirect($url) { header("Location: $url"); exit(); }
function sanitize($data) { return htmlspecialchars(strip_tags($data)); }
?>
