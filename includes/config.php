<?php
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'result_system';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';

$isExternal = !in_array($host, ['localhost', '127.0.0.1', '::1', '']);

if ($isExternal) {
    $mysqli = new mysqli();
    $mysqli->ssl_set(null, null, '/etc/ssl/certs/ca-certificates.crt', null, null);
    $mysqli->real_connect($host, $username, $password, $dbname, (int)$port, MYSQLI_CLIENT_SSL);
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    $pdo = new MysqliPdoWrapper($mysqli);
} else {
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

class MysqliPdoWrapper {
    private $mysqli;

    public function __construct(mysqli $mysqli) {
        $this->mysqli = $mysqli;
    }

    public function prepare($sql) {
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) die("Prepare failed: " . $this->mysqli->error);
        return new MysqliStmtWrapper($stmt);
    }

    public function exec($sql) {
        $this->mysqli->multi_query($sql);
        do {
            if ($result = $this->mysqli->store_result()) $result->free();
        } while ($this->mysqli->more_results() && $this->mysqli->next_result());
        if ($this->mysqli->error) die("Exec failed: " . $this->mysqli->error);
        return $this->mysqli->affected_rows;
    }

    public function lastInsertId() {
        return $this->mysqli->insert_id;
    }

    public function errorInfo() {
        return $this->mysqli->error;
    }
}

class MysqliStmtWrapper {
    private $stmt;

    public function __construct(mysqli_stmt $stmt) {
        $this->stmt = $stmt;
    }

    public function execute($params = []) {
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

    public function fetch($mode = PDO::FETCH_ASSOC) {
        $result = $this->stmt->get_result();
        if (!$result) return false;
        return $result->fetch_assoc();
    }

    public function fetchAll($mode = PDO::FETCH_ASSOC) {
        $result = $this->stmt->get_result();
        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function rowCount() {
        $result = $this->stmt->get_result();
        if (!$result) return 0;
        return $result->num_rows;
    }
}

session_start();

function isLoggedIn() { return isset($_SESSION['user_id']); }
function getUserRole() { return $_SESSION['role'] ?? null; }
function getStudentId() { return $_SESSION['student_id'] ?? null; }
function redirect($url) { header("Location: $url"); exit(); }
function sanitize($data) { return htmlspecialchars(strip_tags($data)); }
?>
