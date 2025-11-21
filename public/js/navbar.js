const token = localStorage.getItem('jwt_token');
const adminLink = document.getElementById('admin-link');

if (token && adminLink) {
    adminLink.textContent = 'Панель администратора';
    adminLink.href = '/admin';
}

