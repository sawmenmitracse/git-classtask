async function fetchCsrfToken() {
    try {
        const response = await fetch('get_csrf_token.php');
        if (!response.ok) throw new Error(`Failed to fetch CSRF token: ${response.status}`);
        const data = await response.json();
        if (!data.csrf_token) throw new Error('CSRF token not found in response');
        console.log('Fetched CSRF Token:', data.csrf_token);
        return data.csrf_token;
    } catch (error) {
        console.error('Error fetching CSRF token:', error);
        alert('Failed to initialize security token. Please refresh the page.');
        return null;
    }
}

async function initializeCsrfTokens() {
    const csrfToken = await fetchCsrfToken();
    if (csrfToken) {
        document.getElementById('loginCsrfToken').value = csrfToken;
        document.getElementById('registerCsrfToken').value = csrfToken;
        document.getElementById('screenTimeCsrfToken').value = csrfToken;
        document.getElementById('loginSubmitBtn').disabled = false;
        document.getElementById('registerSubmitBtn').disabled = false;
        document.getElementById('screenTimeSubmitBtn').disabled = false;
        console.log('CSRF tokens initialized');
    } else {
        console.error('CSRF token initialization failed');
    }
}

function showLogin() {
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('registerForm').style.display = 'none';
}

function showRegister() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
}

document.getElementById('registerSubmitForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const data = {
        name: document.getElementById('registerName').value,
        email: document.getElementById('registerEmail').value,
        password: document.getElementById('registerPassword').value,
        csrf_token: document.getElementById('registerCsrfToken').value
    };
    console.log('Register Data:', data);
    const res = await fetch('register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).catch(error => {
        console.error('Fetch error:', error);
        alert('Network error during registration. Please try again.');
    });
    if (!res) return;
    const result = await res.json();
    console.log('Register Response:', result);
    if (result.success) {
        alert('Registered successfully! You can now log in.');
        showLogin();
    } else {
        alert(result.message || 'Registration failed');
    }
});

document.getElementById('loginSubmitForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const data = {
        email: document.getElementById('loginEmail').value,
        password: document.getElementById('loginPassword').value,
        csrf_token: document.getElementById('loginCsrfToken').value
    };
    console.log('Login Data:', data);
    const res = await fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).catch(error => {
        console.error('Fetch error:', error);
        alert('Network error during login. Please try again.');
    });
    if (!res) return;
    const result = await res.json();
    console.log('Login Response:', result);
    if (result.success) {
        localStorage.setItem('parentName', result.name);
        localStorage.setItem('parentId', result.id);
        loadParentDashboard();
    } else {
        alert(result.message || 'Login failed');
    }
});

function logout() {
    localStorage.clear();
    document.getElementById('authSection').style.display = 'block';
    document.getElementById('dashboardSection').style.display = 'none';
    document.getElementById('logoutBtn').style.display = 'none';
}

function loadParentDashboard() {
    document.getElementById('authSection').style.display = 'none';
    document.getElementById('dashboardSection').style.display = 'block';
    document.getElementById('logoutBtn').style.display = 'inline-block';
    document.getElementById('parentName').textContent = localStorage.getItem('parentName');
}

document.getElementById('screenTimeForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const data = {
        childName: document.getElementById('childName').value,
        screenTime: document.getElementById('screenTime').value,
        csrf_token: document.getElementById('screenTimeCsrfToken').value
    };
    console.log('Screen Time Data:', data);
    const res = await fetch('save_child.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).catch(error => {
        console.error('Fetch error:', error);
        alert('Network error during child data submission. Please try again.');
    });
    if (!res) return;
    const result = await res.json();
    console.log('Screen Time Response:', result);
    if (result.status === 'success') {
        localStorage.setItem('childId', result.child_id);
        window.location.href = `child_dashboard.php?childName=${encodeURIComponent(result.child_name)}`;
    } else {
        alert(result.message || 'Failed to save child data');
    }
});

// Initialize CSRF tokens and check if logged in
window.onload = async function () {
    await initializeCsrfTokens();
    if (localStorage.getItem('parentId')) {
        loadParentDashboard();
    }
};