document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const ma_lop = urlParams.get('ma_lop');
    fetch(`../backend/routes.php?controller=class&action=get_class&ma_lop=${ma_lop}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            document.getElementById('ma_lop').innerText = `Mã lớp: ${data.ma_lop}`;
            document.getElementById('ten_mon').innerText = `Tên môn: ${data.ten_mon}`;
            document.getElementById('hoc_ky').innerText = `Học kỳ: ${data.hoc_ky}`;
            document.getElementById('nam_hoc').innerText = `Năm học: ${data.nam_hoc}`;
            document.getElementById('so_tin_chi').innerText = `Số tín chỉ: ${data.so_tin_chi}`;
            const tbody = document.querySelector('#student_list tbody');
            data.students.forEach(student => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${student.ma_sv}</td>
                    <td>${student.ten_sv}</td>
                    <td><a href="nhapdiem.html?ma_sv=${student.ma_sv}&ma_lop=${ma_lop}">Nhập điểm</a></td>
                `;
            });
        });
});