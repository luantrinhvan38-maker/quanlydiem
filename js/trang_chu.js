document.addEventListener('DOMContentLoaded', function() {
    fetch('../backend/routes.php?controller=class&action=get_all_classes')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#class_list tbody');
            data.forEach(classItem => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${classItem.ma_lop}</td>
                    <td>${classItem.ten_mon}</td>
                    <td>${classItem.hoc_ky}</td>
                    <td>${classItem.nam_hoc}</td>
                    <td><a href="../html/chitietlop.html?ma_lop=${classItem.ma_lop}">Chi tiết</a></td>
                `;
            });
        });
});

function searchClass() {
    const ma_lop = document.getElementById('ma_lop').value;
    fetch(`../backend/routes.php?controller=class&action=get_class&ma_lop=${ma_lop}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                window.location.href = `chitietlop.html?ma_lop=${ma_lop}`;
            }
        });
}