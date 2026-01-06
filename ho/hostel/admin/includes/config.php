<?php
$host = getenv('PGHOST') ?: 'localhost';
$port = getenv('PGPORT') ?: '5432';
$db = getenv('PGDATABASE') ?: 'heliumdb';
$dbuser = getenv('PGUSER') ?: 'postgres';
$dbpass = getenv('PGPASSWORD') ?: '';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);
} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

class MySQLiCompat {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function prepare($sql) {
        $sql = $this->convertSql($sql);
        return new MySQLiStmtCompat($this->pdo, $sql);
    }
    
    public function query($sql) {
        $sql = $this->convertSql($sql);
        $stmt = $this->pdo->query($sql);
        return new MySQLiResultCompat($stmt);
    }
    
    public function real_escape_string($str) {
        return substr($this->pdo->quote($str), 1, -1);
    }
    
    private function convertSql($sql) {
        $sql = preg_replace('/\|\|/', ' OR ', $sql);
        $index = 0;
        $sql = preg_replace_callback('/\?/', function($m) use (&$index) {
            $index++;
            return '$' . $index;
        }, $sql);
        return $sql;
    }
    
    public function __get($name) {
        if ($name === 'insert_id') {
            return $this->pdo->lastInsertId();
        }
        return null;
    }
}

class MySQLiStmtCompat {
    private $pdo;
    private $sql;
    private $stmt;
    private $params = [];
    private $result;
    private $boundVars = [];
    private $currentRow = null;
    
    public function __construct($pdo, $sql) {
        $this->pdo = $pdo;
        $this->sql = $sql;
    }
    
    public function bind_param($types, &...$vars) {
        $this->params = [];
        foreach ($vars as $v) {
            $this->params[] = $v;
        }
        return true;
    }
    
    public function bind_result(&...$vars) {
        $this->boundVars = [];
        foreach ($vars as $key => $val) {
            $this->boundVars[$key] = &$vars[$key];
        }
        return true;
    }
    
    public function execute() {
        $this->stmt = $this->pdo->prepare($this->sql);
        $this->stmt->execute($this->params);
        $this->result = $this->stmt;
        return true;
    }
    
    public function fetch() {
        $row = $this->stmt->fetch(PDO::FETCH_NUM);
        if ($row === false) {
            return false;
        }
        $this->currentRow = $row;
        foreach ($this->boundVars as $key => &$var) {
            if (isset($row[$key])) {
                $var = $row[$key];
            }
        }
        return true;
    }
    
    public function close() {
        $this->stmt = null;
        return true;
    }
    
    public function get_result() {
        return new MySQLiResultCompat($this->result);
    }
    
    public function store_result() {
        return true;
    }
    
    public function __get($name) {
        if ($name === 'num_rows') {
            return $this->stmt ? $this->stmt->rowCount() : 0;
        }
        if ($name === 'affected_rows') {
            return $this->stmt ? $this->stmt->rowCount() : 0;
        }
        return null;
    }
}

class MySQLiResultCompat {
    private $stmt;
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }
    
    public function fetch_object() {
        return $this->stmt ? $this->stmt->fetch(PDO::FETCH_OBJ) : false;
    }
    
    public function fetch_assoc() {
        return $this->stmt ? $this->stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
    
    public function fetch_array($mode = null) {
        return $this->stmt ? $this->stmt->fetch(PDO::FETCH_BOTH) : false;
    }
    
    public function num_rows() {
        return $this->stmt ? $this->stmt->rowCount() : 0;
    }
    
    public function __get($name) {
        if ($name === 'num_rows') {
            return $this->stmt ? $this->stmt->rowCount() : 0;
        }
        return null;
    }
}

$mysqli = new MySQLiCompat($pdo);
$conn = $mysqli;
?>
