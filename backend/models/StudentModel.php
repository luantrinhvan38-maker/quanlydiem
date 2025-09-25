<?php
require_once __DIR__ . '/../config/database.php';

class StudentModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Lấy danh sách sinh viên theo class_id
    public function getStudentsByClass($class_id) {
        $stmt = $this->pdo->prepare("SELECT id, ma_sv, ten_sv FROM students WHERE class_id = ?");
        $stmt->execute([$class_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy sinh viên theo mã SV
    public function getStudentByMaSV($ma_sv) {
        $stmt = $this->pdo->prepare("SELECT id, ma_sv, ten_sv FROM students WHERE ma_sv = ?");
        $stmt->execute([$ma_sv]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['error' => 'Không tìm thấy sinh viên'];
    }

    // Lấy danh sách sinh viên và điểm theo class_id
    public function getStudentsWithScoresByClass($class_id) {
        $stmt = $this->pdo->prepare("SELECT s.ma_sv, s.ten_sv, sc.* 
                                    FROM students s 
                                    LEFT JOIN scores sc ON s.id = sc.student_id 
                                    WHERE s.class_id = ?");
        $stmt->execute([$class_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>