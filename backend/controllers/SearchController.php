<?php
require_once __DIR__ . '/../models/ScoreModel.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/ClassModel.php';

class SearchController {
    private $scoreModel;
    private $studentModel;
    private $classModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->scoreModel = new ScoreModel($pdo);
        $this->studentModel = new StudentModel($pdo);
        $this->classModel = new ClassModel($pdo);
        
        // Kiểm tra kết nối database và cấu trúc bảng
        $this->checkDatabaseStructure();
    }
    
    private function checkDatabaseStructure() {
        try {
            // Kiểm tra bảng classes
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'classes'");
            if (!$stmt->fetch()) {
                throw new Exception("Bảng classes không tồn tại");
            }
            
            // Kiểm tra bảng students
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'students'");
            if (!$stmt->fetch()) {
                throw new Exception("Bảng students không tồn tại");
            }
            
            // Kiểm tra bảng scores
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'scores'");
            if (!$stmt->fetch()) {
                throw new Exception("Bảng scores không tồn tại");
            }
        } catch (PDOException $e) {
            error_log("Lỗi khi kiểm tra cấu trúc database: " . $e->getMessage());
            throw new Exception("Không thể kết nối đến database");
        }
    }

    public function handleRequest($action, $params) {
        try {
            switch ($action) {
                case 'search_by_student':
                    return $this->searchByStudent($params);
                case 'search_by_class':
                    return $this->searchByClass($params);
                case 'statistics':
                    return $this->getClassStatistics($params);
                default:
                    return ['error' => 'Hành động không hợp lệ'];
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi trong quá trình xử lý'];
        }
    }

    private function searchByStudent($params) {
        if (empty($params['ma_sv'])) {
            return ['error' => 'Mã sinh viên không được để trống'];
        }

        $student = $this->studentModel->getStudentByMaSV($params['ma_sv']);
        if (isset($student['error'])) {
            return $student;
        }

        $score = $this->scoreModel->getScoreByStudent($student['id']);
        if (isset($score['error'])) {
            return ['warning' => 'Sinh viên chưa có điểm'];
        }

        return array_merge($student, $score);
    }

    private function searchByClass($params) {
        if (empty($params['class_id'])) {
            return ['error' => 'ID lớp không hợp lệ'];
        }

        try {
            // Kiểm tra lớp tồn tại
            $classCheck = $this->pdo->prepare("SELECT id FROM classes WHERE id = ?");
            $classCheck->execute([$params['class_id']]);
            if (!$classCheck->fetch()) {
                return ['error' => 'Lớp học không tồn tại'];
            }

            // Truy vấn thông tin sinh viên và điểm
            $stmt = $this->pdo->prepare("
                SELECT 
                    s.ma_sv,
                    s.ten_sv,
                    COALESCE(sc.diem_chuyen_can, 0) as diem_chuyen_can,
                    COALESCE(sc.diem_giua_ky_1, 0) as diem_giua_ky_1,
                    sc.diem_giua_ky_2,
                    COALESCE(sc.diem_thao_luan, 0) as diem_thao_luan,
                    COALESCE(sc.diem_cuoi_ky, 0) as diem_cuoi_ky,
                    COALESCE(sc.total_score, 0) as total_score,
                    COALESCE(sc.grade, 'F') as grade,
                    CASE WHEN sc.id IS NULL THEN 0 ELSE 1 END as co_diem
                FROM students s
                LEFT JOIN scores sc ON s.id = sc.student_id
                WHERE s.class_id = ?
                ORDER BY s.ma_sv
            ");
            
            $stmt->execute([$params['class_id']]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                // Kiểm tra xem lớp có sinh viên không
                $studentCheck = $this->pdo->prepare("SELECT COUNT(*) FROM students WHERE class_id = ?");
                $studentCheck->execute([$params['class_id']]);
                $studentCount = $studentCheck->fetchColumn();
                
                if ($studentCount == 0) {
                    return ['warning' => 'Lớp này chưa có sinh viên nào'];
                } else {
                    return ['warning' => 'Không thể truy xuất dữ liệu sinh viên'];
                }
            }

            // Đếm số sinh viên chưa có điểm
            $withoutScores = array_filter($results, function($student) {
                return $student['co_diem'] == 0;
            });
            
            if (count($withoutScores) > 0) {
                $results['warning'] = 'Có ' . count($withoutScores) . ' sinh viên chưa có điểm';
            }

            return $results;
        } catch (PDOException $e) {
            error_log("Lỗi truy vấn searchByClass: " . $e->getMessage());
            throw new Exception('Lỗi khi truy vấn dữ liệu lớp');
        }
    }

    private function getClassStatistics($params) {
        if (empty($params['class_id'])) {
            return ['error' => 'ID lớp không hợp lệ'];
        }

        try {
            // Kiểm tra lớp tồn tại
            $classCheck = $this->pdo->prepare("SELECT id, ma_lop, ten_mon FROM classes WHERE id = ?");
            $classCheck->execute([$params['class_id']]);
            $classInfo = $classCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$classInfo) {
                return ['error' => 'Lớp học không tồn tại'];
            }

            // Lấy tổng số sinh viên trong lớp
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total_students
                FROM students
                WHERE class_id = ?
            ");
            $stmt->execute([$params['class_id']]);
            $totalStudents = $stmt->fetchColumn();

            if ($totalStudents == 0) {
                return [
                    'warning' => 'Lớp này chưa có sinh viên',
                    'stats' => [],
                    'ty_le_dau' => 0,
                    'gpa' => 0,
                    'total_students' => 0,
                    'students_with_scores' => 0,
                    'class_info' => $classInfo
                ];
            }

            // Lấy thống kê điểm và số sinh viên có điểm
            $stmt = $this->pdo->prepare("
                SELECT 
                    COALESCE(sc.grade, 'F') as grade,
                    COUNT(*) as count,
                    SUM(CASE WHEN sc.id IS NOT NULL THEN 1 ELSE 0 END) as has_scores
                FROM students s
                LEFT JOIN scores sc ON s.id = sc.student_id
                WHERE s.class_id = ?
                GROUP BY COALESCE(sc.grade, 'F')
                ORDER BY 
                    CASE grade 
                        WHEN 'A' THEN 1 
                        WHEN 'B' THEN 2 
                        WHEN 'C' THEN 3 
                        WHEN 'D' THEN 4 
                        WHEN 'F' THEN 5 
                    END
            ");
            $stmt->execute([$params['class_id']]);
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Đếm số sinh viên có điểm
            $studentsWithScores = array_sum(array_column($stats, 'has_scores'));

            // Tính GPA trung bình
            $stmt = $this->pdo->prepare("
                WITH student_scores AS (
                    SELECT s.id, COALESCE(sc.total_score, 0) as score
                    FROM students s
                    LEFT JOIN scores sc ON s.id = sc.student_id
                    WHERE s.class_id = ?
                )
                SELECT 
                    ROUND(AVG(score), 2) as gpa,
                    COUNT(CASE WHEN score > 0 THEN 1 END) as students_with_scores
                FROM student_scores
            ");
            $stmt->execute([$params['class_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $gpa = $result['gpa'];

            // Tính tỷ lệ đậu
            $failed = 0;
            foreach ($stats as $stat) {
                if ($stat['grade'] === 'F') {
                    $failed = $stat['count'];
                    break;
                }
            }
            $ty_le_dau = $totalStudents > 0 ? (($totalStudents - $failed) / $totalStudents * 100) : 0;

            return [
                'stats' => $stats,
                'ty_le_dau' => $ty_le_dau,
                'gpa' => $gpa ?: 0,
                'total_students' => $totalStudents,
                'students_with_scores' => $studentsWithScores,
                'message' => $studentsWithScores < $totalStudents ? 
                    'Lưu ý: ' . ($totalStudents - $studentsWithScores) . ' sinh viên chưa có điểm' : null
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['error' => 'Lỗi khi tính toán thống kê'];
        }
    }
}
?>