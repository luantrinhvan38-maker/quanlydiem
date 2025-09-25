let classId = null;

function loadClass() {
    const ma_lop = document.getElementById('ma_lop').value;
    fetch(`../backend/routes.php?controller=class&action=get_class&ma_lop=${ma_lop}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            classId = data.id;
            document.getElementById('class_info').style.display = 'block';
            document.getElementById('ten_mon').innerText = `Tên môn: ${data.ten_mon}`;
            document.getElementById('hoc_ky').innerText = `Học kỳ: ${data.hoc_ky}`;
            document.getElementById('nam_hoc').innerText = `Năm học: ${data.nam_hoc}`;
            document.getElementById('so_tin_chi').innerText = `Số tín chỉ: ${data.so_tin_chi}`;
            const tbody = document.querySelector('#student_list tbody');
            tbody.innerHTML = '';
            data.students.forEach(student => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${student.ma_sv}</td>
                    <td>${student.ten_sv}</td>
                    <td><button onclick="removeStudent('${student.ma_sv}')">Xóa</button></td>
                `;
            });
        });
}

document.getElementById('add_student_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('controller', 'class');
    formData.append('action', 'add_student');
    formData.append('class_id', classId);
    fetch('../backend/routes.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            alert(data.success || data.error);
            if (data.success) loadClass();
        });
});

document.getElementById('remove_student_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('controller', 'class');
    formData.append('action', 'remove_student');
    formData.append('class_id', classId);
    fetch('../backend/routes.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            alert(data.success || data.error);
            if (data.success) loadClass();
        });
});

document.getElementById('change_teacher_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('controller', 'class');
    formData.append('action', 'propose_change_teacher');
    formData.append('class_id', classId);
    fetch('../backend/routes.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => alert(data.success || data.error));
});

document.getElementById('delete_class_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('controller', 'class');
    formData.append('action', 'propose_delete_class');
    formData.append('class_id', classId);
    fetch('../backend/routes.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => alert(data.success || data.warning || data.error));
});