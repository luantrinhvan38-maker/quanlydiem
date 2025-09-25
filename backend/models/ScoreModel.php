<?php
require_once __DIR__ . '/../config/database.php';

class ScoreModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Nhập điểm
    public function enterScore($student_id, $diem_chuyen_can, $diem_giua_ky_1, $diem_giua_ky_2, $diem_thao_luan, $diem_cuoi_ky) {
        // Validation cơ bản
        if (!is_numeric($diem_chuyen_can) || !is_numeric($diem_giua_ky_1) || !is_numeric($diem_thao_luan) || !is_numeric($diem_cuoi_ky)) {
            return ['error' => 'Điểm phải là số'];
        }
        
        if ($diem_giua_ky_2 !== null && !is_numeric($diem_giua_ky_2)) {
            return ['error' => 'Điểm giữa kỳ 2 phải là số'];
        }
        
        // Validation phạm vi điểm
        if ($diem_chuyen_can < 0 || $diem_chuyen_can > 10) {
            return ['error' => 'Điểm chuyên cần phải từ 0 đến 10'];
        }
        
        if ($diem_giua_ky_1 < 0 || $diem_giua_ky_1 > 10) {
            return ['error' => 'Điểm giữa kỳ 1 phải từ 0 đến 10'];
        }
        
        if ($diem_giua_ky_2 !== null && ($diem_giua_ky_2 < 0 || $diem_giua_ky_2 > 10)) {
            return ['error' => 'Điểm giữa kỳ 2 phải từ 0 đến 10'];
        }
        
        if ($diem_thao_luan < 0 || $diem_thao_luan > 10) {
            return ['error' => 'Điểm thảo luận phải từ 0 đến 10'];
        }
        
        if ($diem_cuoi_ky < 0 || $diem_cuoi_ky > 10) {
            return ['error' => 'Điểm cuối kỳ phải từ 0 đến 10'];
        }
        
        // Validation student_id
        if (!is_numeric($student_id) || $student_id <= 0) {
            return ['error' => 'ID sinh viên không hợp lệ'];
        }
        
        // Kiểm tra sinh viên có tồn tại không
        $stmt = $this->pdo->prepare("SELECT id FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        if (!$stmt->fetch()) {
            return ['error' => 'Sinh viên không tồn tại'];
        }

        $stmt = $this->pdo->prepare("INSERT INTO scores (student_id, diem_chuyen_can, diem_giua_ky_1, diem_giua_ky_2, diem_thao_luan, diem_cuoi_ky) 
                                    VALUES (?, ?, ?, ?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE 
                                    diem_chuyen_can = ?, diem_giua_ky_1 = ?, diem_giua_ky_2 = ?, diem_thao_luan = ?, diem_cuoi_ky = ?");
        $stmt->execute([$student_id, $diem_chuyen_can, $diem_giua_ky_1, $diem_giua_ky_2, $diem_thao_luan, $diem_cuoi_ky, 
                        $diem_chuyen_can, $diem_giua_ky_1, $diem_giua_ky_2, $diem_thao_luan, $diem_cuoi_ky]);
        return ['success' => 'Nhập điểm thành công'];
    }

    // Lấy điểm của sinh viên
    public function getScoreByStudent($student_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM scores WHERE student_id = ?");
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['error' => 'Không tìm thấy điểm'];
    }

    // Xóa điểm
    public function deleteScore($student_id) {
        $stmt = $this->pdo->prepare("DELETE FROM scores WHERE student_id = ?");
        $stmt->execute([$student_id]);
        return ['success' => 'Xóa điểm thành công'];
    }

    // Tính điểm tổng kết
    public function calculateScores($class_id) {
        $stmt = $this->pdo->prepare("SELECT so_tin_chi FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        $so_tin_chi = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT s.id, sc.* FROM students s LEFT JOIN scores sc ON s.id = sc.student_id WHERE s.class_id = ?");
        $stmt->execute([$class_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($students as $student) {
            if (!isset($student['diem_chuyen_can']) || !isset($student['diem_giua_ky_1']) || !isset($student['diem_thao_luan']) || !isset($student['diem_cuoi_ky'])) {
                continue; // Bỏ qua nếu thiếu điểm
            }

            $diem_giua_ky = $student['diem_giua_ky_1'];
            if ($so_tin_chi == 3 && isset($student['diem_giua_ky_2'])) {
                $diem_giua_ky = ($student['diem_giua_ky_1'] + $student['diem_giua_ky_2']) / 2;
            }

            $total_score = $student['diem_chuyen_can'] * 0.1 + $student['diem_thao_luan'] * 0.15 + $diem_giua_ky * 0.15 + $student['diem_cuoi_ky'] * 0.6;

            $grade = $total_score >= 8.5 ? 'A' : ($total_score >= 8.0 ? 'B+' : ($total_score >= 7.0 ? 'B' : ($total_score >= 6.5 ? 'C+' : ($total_score >= 5.5 ? 'C' : ($total_score >= 5.0 ? 'D+' : ($total_score >= 4.0 ? 'D' : 'F'))))));

            $stmt = $this->pdo->prepare("UPDATE scores SET total_score = ?, grade = ? WHERE student_id = ?");
            $stmt->execute([$total_score, $grade, $student['id']]);
        }
        return ['success' => 'Tính điểm thành công'];
    }

    // Lấy thống kê điểm theo class_id
    public function getStatisticsByClass($class_id) {
        $stmt = $this->pdo->prepare("SELECT grade, COUNT(*) as count 
                                    FROM scores 
                                    WHERE student_id IN (SELECT id FROM students WHERE class_id = ?) 
                                    GROUP BY grade");
        $stmt->execute([$class_id]);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = array_sum(array_column($stats, 'count'));
        $dau = $total - ($stats[array_search('F', array_column($stats, 'grade'))]['count'] ?? 0);
        $ty_le_dau = $total ? ($dau / $total * 100) : 0;

        $stmt = $this->pdo->prepare("SELECT AVG(total_score) as gpa 
                                    FROM scores 
                                    WHERE student_id IN (SELECT id FROM students WHERE class_id = ?)");
        $stmt->execute([$class_id]);
        $gpa = $stmt->fetchColumn();

        return ['stats' => $stats, 'ty_le_dau' => $ty_le_dau, 'gpa' => $gpa];
    }
}
?>