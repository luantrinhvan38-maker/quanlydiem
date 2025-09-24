document.getElementById('enter_score_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('controller', 'score');
    formData.append('action', 'enter_score');
    fetch('../backend/routes.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => alert(data.success || data.error));
});

function loadScore() {
    const ma_sv = document.querySelector('#edit_score_form input[name="ma_sv"]').value;
    fetch(`../backend/routes.php?controller=score&action=get_score&ma_sv=${ma_sv}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            const scoreInfo = document.getElementById('score_info');
            scoreInfo.style.display = 'block';
            scoreInfo.querySelector('input[name="diem_chuyen_can"]').value = data.diem_chuyen_can;
            scoreInfo.querySelector('input[name="diem_giua_ky_1"]').value = data.diem_giua_ky_1;
            scoreInfo.querySelector('input[name="diem_giua_ky_2"]').value = data.diem_giua_ky_2 || '';
            scoreInfo.querySelector('input[name="diem_thao_luan"]').value = data.diem_thao_luan;
            scoreInfo.querySelector('input[name="diem_cuoi_ky"]').value = data.diem_cuoi_ky;
        });
}

document.getElementById('edit_score_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('controller', 'score');
    formData.append('action', 'enter_score');
    fetch('../backend/routes.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => alert(data.success || data.error));
});

document.getElementById('delete_score_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('controller', 'score');
    formData.append('action', 'delete_score');
    fetch('../backend/routes.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => alert(data.success || data.error));
});

document.getElementById('calculate_score_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const ma_lop = this.querySelector('input[name="ma_lop"]').value;
    fetch(`../backend/routes.php?controller=class&action=get_class&ma_lop=${ma_lop}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            const formData = new FormData();
            formData.append('controller', 'score');
            formData.append('action', 'calculate_scores');
            formData.append('class_id', data.id);
            fetch('../backend/routes.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => alert(data.success || data.error));
        });
});