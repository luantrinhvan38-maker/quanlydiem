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
                return $this->studentModel->getStudentsWithScoresByClass($params['class_id']);
            case 'statistics':
                return $this->scoreModel->getStatisticsByClass($params['class_id']);
            default:
                return ['error' => 'Hành động không hợp lệ'];
        }
    }
}
?>