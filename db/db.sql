-- Tạo database (nếu chưa tồn tại)
CREATE DATABASE IF NOT EXISTS quanlydiem;
USE quanlydiem;

-- Bảng classes (lớp học phần)
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_lop VARCHAR(20) NOT NULL UNIQUE,
    ten_mon VARCHAR(100),
    hoc_ky INT,
    nam_hoc INT,
    so_tin_chi INT CHECK (so_tin_chi IN (2, 3))
);

-- Bảng students (sinh viên trong lớp)
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_sv VARCHAR(20) NOT NULL,
    ten_sv VARCHAR(100),
    class_id INT,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Bảng scores (điểm số)
CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    diem_chuyen_can FLOAT CHECK (diem_chuyen_can BETWEEN 0 AND 10),
    diem_giua_ky_1 FLOAT CHECK (diem_giua_ky_1 BETWEEN 0 AND 10),
    diem_giua_ky_2 FLOAT CHECK (diem_giua_ky_2 BETWEEN 0 AND 10 OR diem_giua_ky_2 IS NULL),
    diem_thao_luan FLOAT CHECK (diem_thao_luan BETWEEN 0 AND 10),
    diem_cuoi_ky FLOAT CHECK (diem_cuoi_ky BETWEEN 0 AND 10),
    total_score FLOAT,
    grade VARCHAR(2),
    FOREIGN KEY (student_id) REFERENCES students(id),
    UNIQUE KEY unique_student_score (student_id),
    INDEX idx_student_id (student_id)
);

-- Bảng logs (lưu lý do cập nhật/đề xuất)
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50),
    reason TEXT,
    class_id INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Dữ liệu mẫu (chỉ thêm nếu chưa tồn tại)
INSERT IGNORE INTO classes (ma_lop, ten_mon, hoc_ky, nam_hoc, so_tin_chi) 
VALUES ('LOP001', 'Toán Cao Cấp', 1, 2025, 3),
       ('LOP002', 'Lập trình C', 1, 2025, 2);

INSERT IGNORE INTO students (ma_sv, ten_sv, class_id) 
VALUES ('SV001', 'Nguyễn Văn A', 1),
       ('SV002', 'Trần Thị B', 1),
       ('SV003', 'Lê Văn C', 2);

INSERT IGNORE INTO scores (student_id, diem_chuyen_can, diem_giua_ky_1, diem_giua_ky_2, diem_thao_luan, diem_cuoi_ky) 
VALUES (1, 9, 7, 8, 8.5, 8),
       (2, 8, 6, 7, 7.5, 7);

-- Thêm các index để tối ưu performance (nếu chưa tồn tại)
ALTER TABLE students ADD INDEX IF NOT EXISTS idx_class_id (class_id);
ALTER TABLE students ADD INDEX IF NOT EXISTS idx_ma_sv (ma_sv);
ALTER TABLE logs ADD INDEX IF NOT EXISTS idx_class_id (class_id);
ALTER TABLE logs ADD INDEX IF NOT EXISTS idx_timestamp (timestamp);
ALTER TABLE classes ADD INDEX IF NOT EXISTS idx_ma_lop (ma_lop);