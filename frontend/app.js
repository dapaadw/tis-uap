const API_BASE_URL = 'http://127.0.0.1:8000/api/v1';

axios.interceptors.request.use(config => {
    const token = localStorage.getItem('jwt_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, error => {
    return Promise.reject(error);
});

document.addEventListener("DOMContentLoaded", () => {
    const token = localStorage.getItem('jwt_token');
    if (token) {
        showApp();
        fetchContainers();
    }

    document.getElementById('containerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        document.getElementById('err_container_id').innerText = '';
        document.getElementById('err_waste_type').innerText = '';
        document.getElementById('err_weight_kg').innerText = '';

        const payload = {
            container_id: document.getElementById('container_id').value,
            waste_type: document.getElementById('waste_type').value,
            weight_kg: document.getElementById('weight_kg').value
        };

        try {
            await axios.post(`${API_BASE_URL}/gateway/containers`, payload);
            alert('Kontainer berhasil disimpan!');
            document.getElementById('containerForm').reset();
            fetchContainers();
        } catch (error) {
            if (error.response) {
                if (error.response.status === 403) {
                    alert('Akses Ditolak: Hanya Admin yang dapat memodifikasi data.');
                } else if (error.response.status === 422) {
                    const errors = error.response.data.errors;
                    if (errors.container_id) document.getElementById('err_container_id').innerText = errors.container_id[0];
                    if (errors.waste_type) document.getElementById('err_waste_type').innerText = errors.waste_type[0];
                    if (errors.weight_kg) document.getElementById('err_weight_kg').innerText = errors.weight_kg[0];
                }
            }
        }
    });
});

async function login() {
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;

    try {
        const response = await axios.post(`${API_BASE_URL}/login`, { email, password });
        localStorage.setItem('jwt_token', response.data.authorization.token);
        localStorage.setItem('user_role', response.data.user.role);
        
        alert('Login Berhasil!');
        showApp(response.data.user.name);
        fetchContainers();
    } catch (error) {
        alert('Login gagal. Periksa email dan password.');
    }
}

function logout() {
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('user_role');
    document.getElementById('loginSection').style.display = 'block';
    document.getElementById('appSection').style.display = 'none';
}

function showApp(userName = "Admin/User") {
    document.getElementById('loginSection').style.display = 'none';
    document.getElementById('appSection').style.display = 'block';
    document.getElementById('userGreeting').innerText = `Sistem WowoClean - Akses Diberikan`;
}

async function fetchContainers() {
    try {
        const response = await axios.get(`${API_BASE_URL}/gateway/containers`);
        renderTable(response.data);
    } catch (error) {
        if (error.response && error.response.status === 401) {
            alert('Sesi habis. Silakan login kembali.');
            logout();
        }
    }
}

function renderTable(data) {
    const tableBody = document.getElementById('dataTable');
    tableBody.innerHTML = '';
    let totalWeight = 0;

    data.forEach(item => {
        totalWeight += parseFloat(item.weight_kg);
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.container_id}</td>
            <td>${item.waste_type}</td>
            <td>${item.weight_kg}</td>
            <td>${item.status}</td>
            <td>
                <button onclick="deleteContainer('${item.container_id}')" style="background: red; color: white; border: none; cursor: pointer;">Hapus</button>
            </td>
        `;
        tableBody.appendChild(tr);
    });

    document.getElementById('totalWeight').innerText = totalWeight;
}

async function deleteContainer(id) {
    if(!confirm('Hapus data ini?')) return;
    try {
        await axios.delete(`${API_BASE_URL}/gateway/containers/${id}`);
        fetchContainers();
    } catch (error) {
        if (error.response && error.response.status === 403) {
            alert('Akses Ditolak: Hanya Admin yang dapat menghapus data.');
        }
    }
}