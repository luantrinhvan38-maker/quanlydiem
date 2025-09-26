<?php
// Bật báo cáo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Bắt tất cả lỗi PHP
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    require_once 'controllers/ClassController.php';
    require_once 'controllers/ScoreController.php';
    require_once 'controllers/SearchController.php';
    require_once 'config/database.php';

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $controller = $_POST['controller'] ?? $_GET['controller'] ?? '';
    
    if (empty($action) || empty($controller)) {
        throw new Exception('Thiếu thông tin controller hoặc action');
    }

    $params = array_merge($_POST, $_GET);

    $controllerInstance = null;
    switch ($controller) {
        case 'class':
            $controllerInstance = new ClassController($pdo);
            break;
        case 'score':
            $controllerInstance = new ScoreController($pdo);
            break;
        case 'search':
            $controllerInstance = new SearchController($pdo);
            break;
        default:
            throw new Exception('Controller không hợp lệ');
    }

    $result = $controllerInstance->handleRequest($action, $params);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Lỗi database: ' . $e->getMessage(),
        'code' => $e->getCode()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>