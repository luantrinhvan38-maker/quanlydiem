<?php
require_once __DIR__ . '/../config/database.php';

class ClassModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Tra cứu lớp theo mã lớp
    public function getClassByMaLop($ma_lop) {
        try {
            // Chuẩn hóa mã lớp
            $ma_lop = trim($ma_lop);
            
            // Kiểm tra mã lớp có hợp lệ
            if (empty($ma_lop)) {
                return ['error' => 'Mã lớp không được để trống'];
            }

            // Truy vấn thông tin lớp và số lượng sinh viên
            $stmt = $this->pdo->prepare("
                SELECT c.*, 
                       COUNT(DISTINCT s.id) as so_sinh_vien,
                       COUNT(DISTINCT sc.id) as so_sinh_vien_co_diem
                FROM classes c
                LEFT JOIN students s ON c.id = s.class_id
                LEFT JOIN scores sc ON s.id = sc.student_id
                WHERE c.ma_lop = ?
                GROUP BY c.id
            ");
            $stmt->execute([$ma_lop]);
            $class = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$class) {
                return ['error' => 'Không tìm thấy lớp với mã ' . htmlspecialchars($ma_lop)];
            }

            // Lấy danh sách sinh viên với thông tin điểm
            $stmt = $this->pdo->prepare("
                SELECT s.ma_sv, s.ten_sv,
                       CASE WHEN COUNT(sc.id) > 0 THEN 1 ELSE 0 END as co_diem
                FROM students s
                LEFT JOIN scores sc ON s.id = sc.student_id
                WHERE s.class_id = ?
                GROUP BY s.id
            ");
            $stmt->execute([$class['id']]);
            $class['students'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $class;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi truy vấn thông tin lớp'];
        }
    }

    // Thêm sinh viên
    public function addStudent($class_id, $ma_sv, $ten_sv, $reason) {
        $stmt = $this->pdo->prepare("INSERT INTO students (ma_sv, ten_sv, class_id) VALUES (?, ?, ?)");
        $stmt->execute([$ma_sv, $ten_sv, $class_id]);
        $this->logAction($class_id, 'add_student', $reason);
        return ['success' => 'Thêm sinh viên thành công'];
    }

    // Bớt sinh viên
    public function removeStudent($class_id, $ma_sv, $reason) {
        $stmt = $this->pdo->prepare("DELETE FROM students WHERE ma_sv = ? AND class_id = ?");
        $stmt->execute([$ma_sv, $class_id]);
        $this->logAction($class_id, 'remove_student', $reason);
        return ['success' => 'Xóa sinh viên thành công'];
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