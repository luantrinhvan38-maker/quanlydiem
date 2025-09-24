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
    const ma_lop = this.querySelector('input[name="ma_lop"]').value;
    fetch(`../backend/routes.php?controller=class&action=get_class&ma_lop=${ma_lop}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            fetch(`../backend/routes.php?controller=search&action=search_by_class&class_id=${data.id}`)
                .then(response => response.json())
                .then(students => {
                    const tbody = document.querySelector('#class_result tbody');
                    tbody.innerHTML = '';
                    students.forEach(student => {
                        const row = tbody.insertRow();
                        row.innerHTML = `
                            <td>${student.ma_sv}</td>
                            <td>${student.ten_sv}</td>
                            <td>${student.diem_chuyen_can || ''}</td>
                            <td>${student.diem_giua_ky_1 || ''}${student.diem_giua_ky_2 ? `, ${student.diem_giua_ky_2}` : ''}</td>
                            <td>${student.diem_thao_luan || ''}</td>
                            <td>${student.diem_cuoi_ky || ''}</td>
                            <td>${student.total_score || ''}</td>
                            <td>${student.grade || ''}</td>
                        `;
                    });
                });
        });
});

document.getElementById('statistics_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const ma_lop = this.querySelector('input[name="ma_lop"]').value;
    fetch(`../backend/routes.php?controller=class&action=get_class&ma_lop=${ma_lop}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            fetch(`../backend/routes.php?controller=search&action=statistics&class_id=${data.id}`)
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('statistics_result');
                    let statsHtml = '<h4>Thống kê</h4>';
                    statsHtml += `<p>Tỷ lệ đậu: ${data.ty_le_dau.toFixed(2)}%</p>`;
                    statsHtml += `<p>GPA trung bình: ${data.gpa ? data.gpa.toFixed(2) : 'Chưa có'}</p>`;
                    statsHtml += '<p>Phân bố điểm chữ:</p><ul>';
                    data.stats.forEach(stat => {
                        statsHtml += `<li>${stat.grade}: ${stat.count}</li>`;
                    });
                    statsHtml += '</ul>';
                    resultDiv.innerHTML = statsHtml;
                });
        });
});