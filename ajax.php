<?php
include 'config.php';

header('Content-Type: application/json');

// Authentication check for all AJAX requests
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

$user_id = getCurrentUserId();
$action = $_GET['action'] ?? '';
$current_dashboard_id = $_SESSION['current_dashboard'] ?? null;

switch($action) {
    case 'add_dashboard':
        $name = $_POST['name'] ?? '';
        if(!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO dashboards (user_id, name) VALUES (?, ?)");
            if($stmt->execute([$user_id, $name])) {
                $new_dashboard_id = $pdo->lastInsertId();
                $_SESSION['current_dashboard'] = $new_dashboard_id;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
        }
        break;

    case 'rename_dashboard':
        $name = $_POST['name'] ?? '';
        if(!empty($name) && $current_dashboard_id) {
            $stmt = $pdo->prepare("UPDATE dashboards SET name = ? WHERE id = ? AND user_id = ?");
            if($stmt->execute([$name, $current_dashboard_id, $user_id])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
        }
        break;

    case 'delete_dashboard':
        if($current_dashboard_id) {
            // Get all dashboards count for this user
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM dashboards WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if($count <= 1) {
                echo json_encode(['success' => false, 'message' => 'You must have at least one dashboard']);
                break;
            }
            
            $stmt = $pdo->prepare("DELETE FROM dashboards WHERE id = ? AND user_id = ?");
            if($stmt->execute([$current_dashboard_id, $user_id])) {
                // Set new current dashboard
                $stmt = $pdo->prepare("SELECT id FROM dashboards WHERE user_id = ? ORDER BY id LIMIT 1");
                $stmt->execute([$user_id]);
                $new_dashboard = $stmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION['current_dashboard'] = $new_dashboard['id'];
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        }
        break;

    case 'add_shortcut':
        $name = $_POST['name'] ?? '';
        $url = $_POST['url'] ?? '';
        
        if(!empty($name) && !empty($url) && $current_dashboard_id) {
            // Verify dashboard belongs to user
            $stmt = $pdo->prepare("SELECT id FROM dashboards WHERE id = ? AND user_id = ?");
            $stmt->execute([$current_dashboard_id, $user_id]);
            
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Invalid dashboard']);
                break;
            }
            
            // Get max sort order
            $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM shortcuts WHERE dashboard_id = ?");
            $stmt->execute([$current_dashboard_id]);
            $max_order = $stmt->fetch(PDO::FETCH_ASSOC)['max_order'] ?? 0;
            
            $stmt = $pdo->prepare("INSERT INTO shortcuts (dashboard_id, name, url, sort_order) VALUES (?, ?, ?, ?)");
            if($stmt->execute([$current_dashboard_id, $name, $url, $max_order + 1])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Name and URL are required']);
        }
        break;

    // Other cases remain similar but with user_id checks...
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>