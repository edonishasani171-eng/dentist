<?php
// 1. Fetch the Database URL from Render environment variables
$db_url = getenv('DATABASE_URL');

// Fallback for reading your local .env file
if (!$db_url && file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            if (trim($name) === 'DATABASE_URL') {
                $db_url = trim($value, '"\' ');
                break;
            }
        }
    }
}

if (!$db_url) {
    die(json_encode(['error' => 'DATABASE_URL environment variable is missing.']));
}

// 2. Parse the Neon PostgreSQL connection string safely
$opts = parse_url($db_url);
if (!$opts) {
    die(json_encode(['error' => 'Database configuration string is invalid.']));
}

$host = $opts["host"];
$port = isset($opts["port"]) ? $opts["port"] : "5432";
$user = $opts["user"];
$pass = isset($opts["pass"]) ? $opts["pass"] : "";
$db   = ltrim($opts["path"], '/');

// Neon strings sometimes pass multiple routing parameters; extract just the database name clean
if (strpos($db, '?') !== false) {
    list($db, $extra) = explode('?', $db, 2);
}

// 3. Establish the Real PDO Connection to Neon
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Neon PostgreSQL connection failed: ' . $e->getMessage()]));
}

// 4. Robust MySQLi Compatibility Layer ($conn)
// This translates standard $conn->query() calls into PostgreSQL PDO commands smoothly.
if (!class_exists('NeonMysqliBridge')) {
    class NeonMysqliBridge {
        private $pdo;
        public $error;

        public function __construct($pdo) { 
            $this->pdo = $pdo; 
        }
        
        // Emulate standard procedural/OOP query formats
        public function query($sql) { 
            try {
                return $this->pdo->query($sql); 
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }
        
        public function prepare($sql) { 
            // Convert MySQL parameter marker '?' to Postgres '$1, $2' if needed, or pass-thru
            return $this->pdo->prepare($sql); 
        }
        
        public function real_escape_string($str) { 
            // Avoid syntax breaks by handling single quotes safely
            return str_replace("'", "''", $str); 
        }
        
        public function set_charset($charset) { 
            return true; 
        }
    }
}

// Global database connection variable used throughout your dentist script
$conn = new NeonMysqliBridge($pdo);
?>