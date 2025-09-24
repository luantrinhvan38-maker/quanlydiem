<?php
require_once 'controllers/ClassController.php';
require_once 'controllers/ScoreController.php';
require_once 'controllers/SearchController.php';
require_once 'config/database.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = $_POST['controller'] ?? $_GET['controller'] ?? '';

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
        echo json_encode(['error' => 'Controller không hợp lệ']);
        exit;
}

echo json_encode($controllerInstance->handleRequest($action, $params));
?>