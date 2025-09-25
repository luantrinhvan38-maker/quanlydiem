<?php
require_once __DIR__ . '/../config/database.php';

class LogModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addLog($class_id, $action, $reason) {
        $stmt = $this->pdo->prepare("INSERT INTO logs (action, reason, class_id) VALUES (?, ?, ?)");
        $stmt->execute([$action, $reason, $class_id]);
    }
}
?>