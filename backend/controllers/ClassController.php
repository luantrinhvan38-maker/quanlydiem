<?php
require_once __DIR__ . '/../models/ClassModel.php';

class ClassController {
    private $classModel;

    public function __construct($pdo) {
        $this->classModel = new ClassModel($pdo);
    }

    public function handleRequest($action, $params) {
        try {
            switch ($action) {
                case 'get_class':
                    if (!isset($params['ma_lop'])) {
                        return ['error' => 'Thiếu mã lớp'];
                    }
                    $result = $this->classModel->getClassByMaLop($params['ma_lop']);
                    if (!$result) {
                        return ['error' => 'Không tìm thấy lớp'];
                    }
                    return $result;
                case 'add_student':
                    return $this->classModel->addStudent($params['class_id'], $params['ma_sv'], $params['ten_sv'], $params['reason']);
                case 'remove_student':
                    return $this->classModel->removeStudent($params['class_id'], $params['ma_sv'], $params['reason']);
                case 'propose_change_teacher':
                    return $this->classModel->proposeChangeTeacher($params['class_id'], $params['reason']);
                case 'propose_delete_class':
                    return $this->classModel->proposeDeleteClass($params['class_id'], $params['reason']);
                case 'get_all_classes':
                    return $this->classModel->getAllClasses();
                default:
                    return ['error' => 'Hành động không hợp lệ'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>