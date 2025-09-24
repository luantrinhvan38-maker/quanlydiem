<?php
require_once '../models/ScoreModel.php';
require_once '../models/StudentModel.php';

class ScoreController {
    private $scoreModel;
    private $studentModel;

    public function __construct($pdo) {
        $this->scoreModel = new ScoreModel($pdo);
        $this->studentModel = new StudentModel($pdo);
    }

    public function handleRequest($action, $params) {
        switch ($action) {
            case 'enter_score':
                $student = $this->studentModel->getStudentByMaSV($params['ma_sv']);
                if (isset($student['error'])) {
                    return $student;
                }
                return $this->scoreModel->enterScore($student['id'], $params['diem_chuyen_can'], $params['diem_giua_ky_1'], 
                                                    $params['diem_giua_ky_2'], $params['diem_thao_luan'], $params['diem_cuoi_ky']);
            case 'get_score':
                $student = $this->studentModel->getStudentByMaSV($params['ma_sv']);
                if (isset($student['error'])) {
                    return $student;
                }
                return $this->scoreModel->getScoreByStudent($student['id']);
            case 'delete_score':
                $student = $this->studentModel->getStudentByMaSV($params['ma_sv']);
                if (isset($student['error'])) {
                    return $student;
                }
                return $this->scoreModel->deleteScore($student['id']);
            case 'calculate_scores':
                return $this->scoreModel->calculateScores($params['class_id']);
            default:
                return ['error' => 'Hành động không hợp lệ'];
        }
    }
}
?>