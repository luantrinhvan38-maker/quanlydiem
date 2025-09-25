document.addEventListener('DOMContentLoaded', function() {
    loadClasses();
});

function loadClasses() {
    const tbody = document.querySelector('#class_list tbody');
    tbody.innerHTML = '<tr><td colspan="5">Đang tải...</td></tr>';
    
    fetch('backend/routes.php?controller=class&action=get_all_classes')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5">Không có lớp nào</td></tr>';
                return;
            }
            data.forEach(classItem => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${classItem.ma_lop}</td>
                    <td>${classItem.ten_mon}</td>
                    <td>${classItem.hoc_ky}</td>
                    <td>${classItem.nam_hoc}</td>
                    <td><a href="chitietlop.html?ma_lop=${classItem.ma_lop}">Chi tiết</a></td>
                `;
            });
        })
        .catch(error => {
            console.error('Error loading classes:', error);
            tbody.innerHTML = '<tr><td colspan="5">Lỗi tải dữ liệu: ' + error.message + '</td></tr>';
        });
}

function searchClass() {
    const ma_lop = document.getElementById('ma_lop').value.trim();
    if (!ma_lop) {
        alert('Vui lòng nhập mã lớp');
        return;
    }
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Đang tìm...';
    button.disabled = true;
    
    fetch(`backend/routes.php?controller=class&action=get_class&ma_lop=${ma_lop}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                window.location.href = `chitietlop.html?ma_lop=${ma_lop}`;
            }
        })
        .catch(error => {
            console.error('Error searching class:', error);
            alert('Lỗi tìm kiếm: ' + error.message);
        })
        .finally(() => {
            button.textContent = originalText;
            button.disabled = false;
        });
}