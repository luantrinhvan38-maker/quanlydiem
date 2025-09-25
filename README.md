## Các lỗi đã được khắc phục

### Lỗi đường dẫn file (Critical)
**Vấn đề**: Các controller không thể tìm thấy file model do đường dẫn không chính xác.

**Nguyên nhân**: 
- Khi `routes.php` gọi các controller, đường dẫn tương đối `../models/` không hoạt động đúng
- PHP tìm kiếm từ thư mục gốc thay vì từ thư mục chứa file

**Giải pháp**:
```php
// Trước (LỖI)
require_once '../models/ClassModel.php';

// Sau (ĐÚNG)
require_once __DIR__ . '/../models/ClassModel.php';
```

**Các file đã sửa**:
- `backend/controllers/ClassController.php`
- `backend/controllers/ScoreController.php` 
- `backend/controllers/SearchController.php`
- `backend/models/ClassModel.php`
- `backend/models/ScoreModel.php`
- `backend/models/StudentModel.php`
- `backend/models/LogModel.php`

**Kết quả**: Hệ thống có thể chạy được mà không gặp lỗi fatal error.

### Lỗi truy cập thuộc tính private (High)
**Vấn đề**: SearchController không thể truy cập trực tiếp thuộc tính `$pdo` private trong các model.

**Nguyên nhân**: 
- SearchController cố gắng truy cập `$this->studentModel->pdo` và `$this->scoreModel->pdo`
- Thuộc tính `$pdo` được khai báo là `private` trong các model
- Vi phạm nguyên tắc encapsulation trong OOP

**Giải pháp**:
```php
// Trước (LỖI)
$stmt = $this->studentModel->pdo->prepare("SELECT ...");

// Sau (ĐÚNG)
return $this->studentModel->getStudentsWithScoresByClass($class_id);
```

**Các file đã sửa**:
- `backend/models/StudentModel.php`: Thêm method `getStudentsWithScoresByClass()`
- `backend/models/ScoreModel.php`: Thêm method `getStatisticsByClass()`
- `backend/controllers/SearchController.php`: Sử dụng method thay vì truy cập trực tiếp

**Kết quả**: Tuân thủ nguyên tắc OOP, code sạch hơn và dễ bảo trì.

### Lỗi function thiếu (High)
**Vấn đề**: Function `removeStudent()` được gọi trong HTML nhưng không được định nghĩa trong JavaScript.

**Nguyên nhân**: 
- File `quanlylop.html` có button với `onclick="removeStudent('${student.ma_sv}')"`
- File `quanlylop.js` không có function `removeStudent()` được định nghĩa
- Gây lỗi JavaScript khi người dùng click button "Xóa"

**Giải pháp**:
```javascript
// Thêm function removeStudent() vào quanlylop.js
function removeStudent(ma_sv) {
    if (!confirm(`Bạn có chắc muốn xóa sinh viên ${ma_sv}?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('controller', 'class');
    formData.append('action', 'remove_student');
    formData.append('class_id', classId);
    formData.append('ma_sv', ma_sv);
    formData.append('reason', 'Xóa sinh viên từ danh sách');
    
    fetch('../backend/routes.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            alert(data.success || data.error);
            if (data.success) loadClass();
        });
}
```

**Các file đã sửa**:
- `js/quanlylop.js`: Thêm function `removeStudent()` với confirmation dialog

**Kết quả**: Button "Xóa" hoạt động bình thường, có xác nhận trước khi xóa sinh viên.

### Lỗi cấu trúc database (Medium)
**Vấn đề**: Bảng `scores` thiếu unique constraint, có thể tạo duplicate records cho cùng một sinh viên.

**Nguyên nhân**: 
- Bảng `scores` không có unique constraint trên `student_id`
- ScoreModel sử dụng `ON DUPLICATE KEY UPDATE` nhưng không có key để duplicate
- Có thể tạo nhiều bản ghi điểm cho cùng một sinh viên
- Thiếu index để tối ưu performance

**Giải pháp**:
```sql
-- Thêm NOT NULL và UNIQUE constraint cho student_id
ALTER TABLE scores MODIFY COLUMN student_id INT NOT NULL;
ALTER TABLE scores ADD UNIQUE KEY unique_student_score (student_id);
ALTER TABLE scores ADD INDEX idx_student_id (student_id);

-- Thêm các index khác để tối ưu performance
ALTER TABLE students ADD INDEX idx_class_id (class_id);
ALTER TABLE students ADD INDEX idx_ma_sv (ma_sv);
ALTER TABLE logs ADD INDEX idx_class_id (class_id);
ALTER TABLE logs ADD INDEX idx_timestamp (timestamp);
ALTER TABLE classes ADD INDEX idx_ma_lop (ma_lop);
```

**Các file đã sửa**:
- `db/db.sql`: Thêm unique constraint, NOT NULL và các index

**Kết quả**: 
- Tránh duplicate records cho cùng một sinh viên
- `ON DUPLICATE KEY UPDATE` hoạt động đúng
- Tối ưu performance với index
- Đảm bảo tính toàn vẹn dữ liệu

### Lỗi code JavaScript lạ trong HTML (Medium)
**Vấn đề**: Tất cả file HTML đều có đoạn code JavaScript lạ về XLSX processing không liên quan đến chức năng.

**Nguyên nhân**: 
- Code JavaScript về XLSX processing được thêm vào đầu tất cả file HTML
- Code này không liên quan đến chức năng quản lý điểm
- Có thể gây conflict với JavaScript chính của ứng dụng
- Làm tăng kích thước file không cần thiết

**Giải pháp**:
```html
<!-- Trước (LỖI) -->
<script type="text/javascript">
    var gk_isXlsx = false;
    var gk_xlsxFileLookup = {};
    // ... 40 dòng code XLSX không liên quan
</script>

<!-- Sau (ĐÚNG) -->
<!-- Xóa hoàn toàn đoạn code không liên quan -->
```

**Các file đã sửa**:
- `html/trang_chu.html`: Xóa code JavaScript lạ
- `html/chitietlop.html`: Xóa code JavaScript lạ
- `html/quanlylop.html`: Xóa code JavaScript lạ
- `html/nhapdiem.html`: Xóa code JavaScript lạ
- `html/thongketra.html`: Xóa code JavaScript lạ
- `html/danhsachlop.html`: Xóa code JavaScript lạ

**Kết quả**: 
- File HTML sạch sẽ, chỉ chứa code cần thiết
- Không còn conflict với JavaScript chính
- Giảm kích thước file
- Dễ đọc và bảo trì hơn

### Lỗi thiếu error handling trong JavaScript (Medium)
**Vấn đề**: JavaScript thiếu error handling, không có loading states và validation cơ bản.

**Nguyên nhân**: 
- Không có try-catch cho fetch requests
- Không có loading states khi đang xử lý
- Không validate input trước khi gửi
- Không xử lý lỗi network hoặc server

**Giải pháp**:
```javascript
// Trước (LỖI)
fetch('../backend/routes.php?controller=class&action=get_all_classes')
    .then(response => response.json())
    .then(data => {
        // Xử lý data
    });

// Sau (ĐÚNG)
fetch('../backend/routes.php?controller=class&action=get_all_classes')
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Xử lý data
    })
    .catch(error => {
        console.error('Error loading classes:', error);
        // Hiển thị lỗi cho user
    });
```

**Các file đã sửa**:
- `js/trang_chu.js`: Thêm error handling và loading states
- `js/nhapdiem.js`: Thêm validation input và error handling

**Kết quả**: 
- User experience tốt hơn với loading states
- Xử lý lỗi gracefully
- Validation input cơ bản
- Debug dễ dàng hơn với console.error

### Lỗi validation trong Backend (Medium)
**Vấn đề**: Backend thiếu validation dữ liệu đầu vào, có thể gây lỗi hoặc dữ liệu không hợp lệ.

**Nguyên nhân**: 
- Không validate input trước khi xử lý
- Không kiểm tra dữ liệu có tồn tại trong database
- Không xử lý exception từ database
- Thiếu validation phạm vi điểm

**Giải pháp**:
```php
// Trước (LỖI)
public function addStudent($class_id, $ma_sv, $ten_sv, $reason) {
    $stmt = $this->pdo->prepare("INSERT INTO students (ma_sv, ten_sv, class_id) VALUES (?, ?, ?)");
    $stmt->execute([$ma_sv, $ten_sv, $class_id]);
    return ['success' => 'Thêm sinh viên thành công'];
}

// Sau (ĐÚNG)
public function addStudent($class_id, $ma_sv, $ten_sv, $reason) {
    // Validation input
    if (empty($ma_sv) || empty($ten_sv)) {
        return ['error' => 'Mã sinh viên và tên sinh viên không được để trống'];
    }
    
    // Kiểm tra lớp có tồn tại không
    $stmt = $this->pdo->prepare("SELECT id FROM classes WHERE id = ?");
    $stmt->execute([$class_id]);
    if (!$stmt->fetch()) {
        return ['error' => 'Lớp không tồn tại'];
    }
    
    try {
        $stmt = $this->pdo->prepare("INSERT INTO students (ma_sv, ten_sv, class_id) VALUES (?, ?, ?)");
        $stmt->execute([$ma_sv, $ten_sv, $class_id]);
        return ['success' => 'Thêm sinh viên thành công'];
    } catch (PDOException $e) {
        return ['error' => 'Lỗi thêm sinh viên: ' . $e->getMessage()];
    }
}
```

**Các file đã sửa**:
- `backend/models/ScoreModel.php`: Thêm validation điểm và student_id
- `backend/models/ClassModel.php`: Thêm validation input và kiểm tra tồn tại

**Kết quả**: 
- Đảm bảo dữ liệu đầu vào hợp lệ
- Tránh lỗi database do dữ liệu không hợp lệ
- Thông báo lỗi rõ ràng cho user
- Xử lý exception gracefully

### Lỗi database đã tồn tại (Medium)
**Vấn đề**: Script SQL báo lỗi khi database hoặc bảng đã tồn tại.

**Nguyên nhân**: 
- Lệnh `CREATE DATABASE` không xử lý trường hợp database đã tồn tại
- Lệnh `CREATE TABLE` không xử lý trường hợp bảng đã tồn tại
- Lệnh `INSERT` không xử lý trường hợp dữ liệu đã tồn tại
- Lệnh `ALTER TABLE ADD INDEX` không xử lý trường hợp index đã tồn tại

**Giải pháp**:
```sql
-- Trước (LỖI)
CREATE DATABASE quanlydiem;
CREATE TABLE classes (...);
INSERT INTO classes VALUES (...);
ALTER TABLE students ADD INDEX idx_class_id (class_id);

-- Sau (ĐÚNG)
CREATE DATABASE IF NOT EXISTS quanlydiem;
CREATE TABLE IF NOT EXISTS classes (...);
INSERT IGNORE INTO classes VALUES (...);
ALTER TABLE students ADD INDEX IF NOT EXISTS idx_class_id (class_id);
```

**Các file đã sửa**:
- `db/db.sql`: Thêm `IF NOT EXISTS` và `IGNORE` cho tất cả lệnh

**Kết quả**: 
- Script SQL chạy được nhiều lần mà không báo lỗi
- Không ghi đè dữ liệu hiện có
- Phù hợp cho cả database mới và database đã có
- Dễ dàng cập nhật database