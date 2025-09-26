document.getElementById('search_student_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const ma_sv = this.querySelector('input[name="ma_sv"]').value;
    fetch(`../backend/routes.php?controller=search&action=search_by_student&ma_sv=${ma_sv}`)
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('student_result');
            if (data.error) {
                resultDiv.innerHTML = `<p>${data.error}</p>`;
                return;
            }
            resultDiv.innerHTML = `
                <p>Mã SV: ${data.ma_sv}</p>
                <p>Tên SV: ${data.ten_sv}</p>
                <p>Điểm chuyên cần: ${data.diem_chuyen_can}</p>
                <p>Điểm giữa kỳ: ${data.diem_giua_ky_1}${data.diem_giua_ky_2 ? `, ${data.diem_giua_ky_2}` : ''}</p>
                <p>Điểm thảo luận: ${data.diem_thao_luan}</p>
                <p>Điểm cuối kỳ: ${data.diem_cuoi_ky}</p>
                <p>Điểm tổng kết: ${data.total_score}</p>
                <p>Xếp loại: ${data.grade}</p>
            `;
        });
});

document.getElementById('search_class_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const ma_lop = this.querySelector('input[name="ma_lop"]').value.trim();
    const tbody = document.querySelector('#class_result tbody');
    tbody.innerHTML = '';

    if (!ma_lop) {
        alert('Vui lòng nhập mã lớp');
        return;
    }

    // Thêm loading indicator
    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center">Đang tải dữ liệu...</td></tr>';

    fetch(`../backend/routes.php?controller=class&action=get_class&ma_lop=${encodeURIComponent(ma_lop)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            // Hiển thị thông tin cơ bản của lớp
            document.getElementById('class_info')?.remove();
            const classInfo = document.createElement('div');
            classInfo.id = 'class_info';
            classInfo.innerHTML = `
                <h4>Thông tin lớp: ${data.ma_lop}</h4>
                <p>Tên môn: ${data.ten_mon}</p>
                <p>Học kỳ: ${data.hoc_ky} - Năm học: ${data.nam_hoc}</p>
                <p>Số tín chỉ: ${data.so_tin_chi}</p>
                <p>Tổng số sinh viên: ${data.so_sinh_vien}</p>
            `;
            document.querySelector('#class_result').insertBefore(classInfo, document.querySelector('#class_result table'));
            
            return fetch(`../backend/routes.php?controller=search&action=search_by_class&class_id=${data.id}`);
        })
        .then(response => response.json())
        .then(students => {
            tbody.innerHTML = '';
            
            if (students.warning) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align: center">${students.warning}</td></tr>`;
                return;
            }

            students.forEach(student => {
                const row = tbody.insertRow();
                const hasScores = student.diem_chuyen_can || student.diem_giua_ky_1 || 
                                student.diem_thao_luan || student.diem_cuoi_ky;
                row.innerHTML = `
                    <td>${student.ma_sv || ''}</td>
                    <td>${student.ten_sv || ''}</td>
                    <td>${student.diem_chuyen_can || '0'}</td>
                    <td>${student.diem_giua_ky_1 || '0'}${student.diem_giua_ky_2 ? `, ${student.diem_giua_ky_2}` : ''}</td>
                    <td>${student.diem_thao_luan || '0'}</td>
                    <td>${student.diem_cuoi_ky || '0'}</td>
                    <td>${student.total_score || '0'}</td>
                    <td>${student.grade || 'F'}</td>
                `;
                if (!hasScores) {
                    row.classList.add('no-scores');
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center">Lỗi: ${error.message}</td></tr>`;
        });
});

document.getElementById('statistics_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const ma_lop = this.querySelector('input[name="ma_lop"]').value.trim();
    const resultDiv = document.getElementById('statistics_result');
    resultDiv.innerHTML = '';

    if (!ma_lop) {
        alert('Vui lòng nhập mã lớp');
        return;
    }

    // Thêm loading indicator
    resultDiv.innerHTML = '<p>Đang tải thống kê...</p>';

    fetch(`../backend/routes.php?controller=class&action=get_class&ma_lop=${encodeURIComponent(ma_lop)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            return fetch(`../backend/routes.php?controller=search&action=statistics&class_id=${data.id}`);
        })
        .then(response => response.json())
        .then(data => {
            let statsHtml = `
                <div class="statistics-container">
                    <h4>Thống kê kết quả học tập</h4>
                    <div class="statistics-summary">
                        <p>Tổng số sinh viên: ${data.total_students}</p>
                        <p>Số sinh viên có điểm: ${data.students_with_scores}</p>
                        <p>Tỷ lệ đậu: <strong>${data.ty_le_dau.toFixed(2)}%</strong></p>
                        <p>GPA trung bình: <strong>${data.gpa.toFixed(2)}</strong></p>
                    </div>
            `;

            if (data.message) {
                statsHtml += `<div class="statistics-warning">${data.message}</div>`;
            }

            if (data.stats && data.stats.length > 0) {
                statsHtml += '<div class="grade-distribution"><h5>Phân bố điểm chữ:</h5><ul>';
                const gradeOrder = ['A', 'B', 'C', 'D', 'F'];
                const gradeMap = {};
                data.stats.forEach(stat => {
                    gradeMap[stat.grade] = stat.count;
                });

                gradeOrder.forEach(grade => {
                    const count = gradeMap[grade] || 0;
                    const percent = (count / data.total_students * 100).toFixed(1);
                    statsHtml += `
                        <li class="grade-item grade-${grade.toLowerCase()}">
                            <span class="grade-label">${grade}</span>
                            <span class="grade-count">${count} sinh viên (${percent}%)</span>
                            <div class="grade-bar" style="width: ${percent}%"></div>
                        </li>
                    `;
                });
                statsHtml += '</ul></div>';
            } else {
                statsHtml += '<p>Chưa có dữ liệu điểm</p>';
            }

            statsHtml += '</div>';
            resultDiv.innerHTML = statsHtml;
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = `<div class="error-message">Lỗi: ${error.message}</div>`;
        });
});