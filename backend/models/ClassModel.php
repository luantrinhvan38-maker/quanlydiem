<?php
require_once __DIR__ . '/../config/database.php';

class ClassModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Tra cứu lớp theo mã lớp
    public function getClassByMaLop($ma_lop) {
        $stmt = $this->pdo->prepare("SELECT * FROM classes WHERE ma_lop = ?");
        $stmt->execute([$ma_lop]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($class) {
            // Lấy danh sách sinh viên
            $stmt = $this->pdo->prepare("SELECT ma_sv, ten_sv FROM students WHERE class_id = ?");
            $stmt->execute([$class['id']]);
            $class['students'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $class ?: ['error' => 'Không tìm thấy lớp'];
    }

    // Thêm sinh viên
    public function addStudent($class_id, $ma_sv, $ten_sv, $reason) {
        // Validation input
        if (empty($ma_sv) || empty($ten_sv)) {
            return ['error' => 'Mã sinh viên và tên sinh viên không được để trống'];
        }
        
        if (empty($reason)) {
            return ['error' => 'Lý do không được để trống'];
        }
        
        if (!is_numeric($class_id) || $class_id <= 0) {
            return ['error' => 'ID lớp không hợp lệ'];
        }
        
        // Kiểm tra lớp có tồn tại không
        $stmt = $this->pdo->prepare("SELECT id FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        if (!$stmt->fetch()) {
            return ['error' => 'Lớp không tồn tại'];
        }
        
        // Kiểm tra sinh viên đã tồn tại trong lớp chưa
        $stmt = $this->pdo->prepare("SELECT id FROM students WHERE ma_sv = ? AND class_id = ?");
        $stmt->execute([$ma_sv, $class_id]);
        if ($stmt->fetch()) {
            return ['error' => 'Sinh viên đã tồn tại trong lớp này'];
        }
        
        try {
            $stmt = $this->pdo->prepare("INSERT INTO students (ma_sv, ten_sv, class_id) VALUES (?, ?, ?)");
            $stmt->execute([$ma_sv, $ten_sv, $class_id]);
            $this->logAction($class_id, 'add_student', $reason);
            return ['success' => 'Thêm sinh viên thành công'];
        } catch (PDOException $e) {
            return ['error' => 'Lỗi thêm sinh viên: ' . $e->getMessage()];
        }
    }

    // Bớt sinh viên
    public function removeStudent($class_id, $ma_sv, $reason) {
        // Validation input
        if (empty($ma_sv)) {
            return ['error' => 'Mã sinh viên không được để trống'];
        }
        
        if (empty($reason)) {
            return ['error' => 'Lý do không được để trống'];
        }
        
        if (!is_numeric($class_id) || $class_id <= 0) {
            return ['error' => 'ID lớp không hợp lệ'];
        }
        
        // Kiểm tra sinh viên có tồn tại trong lớp không
        $stmt = $this->pdo->prepare("SELECT id FROM students WHERE ma_sv = ? AND class_id = ?");
        $stmt->execute([$ma_sv, $class_id]);
        if (!$stmt->fetch()) {
            return ['error' => 'Sinh viên không tồn tại trong lớp này'];
        }
        
        try {
            $stmt = $this->pdo->prepare("DELETE FROM students WHERE ma_sv = ? AND class_id = ?");
            $stmt->execute([$ma_sv, $class_id]);
            $this->logAction($class_id, 'remove_student', $reason);
            return ['success' => 'Xóa sinh viên thành công'];
        } catch (PDOException $e) {
            return ['error' => 'Lỗi xóa sinh viên: ' . $e->getMessage()];
        }
    }

    // Đề xuất thay đổi giảng viên
    public function proposeChangeTeacher($class_id, $reason) {
        $this->logAction($class_id, 'change_teacher', $reason);
        return ['success' => 'Đề xuất thay đổi giảng viên thành công'];
    }

    // Đề xuất xóa lớp
    public function proposeDeleteClass($class_id, $reason) {
        // Kiểm tra có điểm không
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM scores WHERE student_id IN (SELECT id FROM students WHERE class_id = ?)");
        $stmt->execute([$class_id]);
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            return ['warning' => 'Lớp có dữ liệu điểm, không thể xóa trực tiếp'];
        }
        $this->logAction($class_id, 'delete_class', $reason);
        return ['success' => 'Đề xuất xóa lớp thành công'];
    }

    // Lưu log
    private function logAction($class_id, $action, $reason) {
        $stmt = $this->pdo->prepare("INSERT INTO logs (action, reason, class_id) VALUES (?, ?, ?)");
        $stmt->execute([$action, $reason, $class_id]);
    }

    // Lấy tất cả lớp
    public function getAllClasses() {
        $stmt = $this->pdo->prepare("SELECT * FROM classes");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>