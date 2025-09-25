<?php
require_once __DIR__ . '/../models/ScoreModel.php';
require_once __DIR__ . '/../models/StudentModel.php';

class SearchController {
    private $scoreModel;
    private $studentModel;

    public function __construct($pdo) {
        $this->scoreModel = new ScoreModel($pdo);
        $this->studentModel = new StudentModel($pdo);
    }

    public function handleRequest($action, $params) {
        switch ($action) {
            case 'search_by_student':
                $student = $this->studentModel->getStudentByMaSV($params['ma_sv']);
                if (isset($student['error'])) {
                    return $student;
                }
                $score = $this->scoreModel->getScoreByStudent($student['id']);
                if (isset($score['error'])) {
                    return $score;
                }
                return array_merge($student, $score);
            case 'search_by_class':
                $stmt = $this->studentModel->pdo->prepare("SELECT s.ma_sv, s.ten_sv, sc.* 
                                                        FROM students s 
                                                        LEFT JOIN scores sc ON s.id = sc.student_id 
                                                        WHERE s.class_id = ?");
                $stmt->execute([$params['class_id']]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            case 'statistics':
                $stmt = $this->scoreModel->pdo->prepare("SELECT grade, COUNT(*) as count 
                                                        FROM scores 
                                                        WHERE student_id IN (SELECT id FROM students WHERE class_id = ?) 
                                                        GROUP BY grade");
                $stmt->execute([$params['class_id']]);
                $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $total = array_sum(array_column($stats, 'count'));
                $dau = $total - ($stats[array_search('F', array_column($stats, 'grade'))]['count'] ?? 0);
                $ty_le_dau = $total ? ($dau / $total * 100) : 0;

                $stmt = $this->scoreModel->pdo->prepare("SELECT AVG(total_score) as gpa 
                                                        FROM scores 
                                                        WHERE student_id IN (SELECT id FROM students WHERE class_id = ?)");
                $stmt->execute([$params['class_id']]);
                $gpa = $stmt->fetchColumn();

                return ['stats' => $stats, 'ty_le_dau' => $ty_le_dau, 'gpa' => $gpa];
            default:
                return ['error' => 'Hành động không hợp lệ'];
        }
    }
}
?>