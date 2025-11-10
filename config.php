<?php
// config.php
session_start();

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'katpaal_dashboard';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Authentication check
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectToLogin() {
    header('Location: login.php');
    exit();
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Default dashboard ID for current user
if (isLoggedIn() && !isset($_SESSION['current_dashboard'])) {
    $user_id = getCurrentUserId();
    $stmt = $pdo->prepare("SELECT id FROM dashboards WHERE user_id = ? ORDER BY id LIMIT 1");
    $stmt->execute([$user_id]);
    $dashboard = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['current_dashboard'] = $dashboard['id'] ?? null;
}
?>