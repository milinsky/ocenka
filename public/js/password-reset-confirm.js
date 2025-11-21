document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('password-confirm-form');
    const tokenInput = document.getElementById('token');
    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const submitBtn = document.getElementById('submit-btn');
    const messageDiv = document.getElementById('message');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const token = tokenInput.value;
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (newPassword.length < 8) {
            showMessage('Пароль должен содержать минимум 8 символов', 'danger');
            return;
        }

        if (newPassword !== confirmPassword) {
            showMessage('Пароли не совпадают', 'danger');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Установка...';
        hideMessage();

        try {
            const response = await fetch('/api/auth/password-reset/confirm', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ token, newPassword }),
            });

            const data = await response.json();

            if (response.ok) {
                showMessage('Пароль успешно изменён! Сейчас вы будете перенаправлены на страницу входа...', 'success');
                form.reset();
                setTimeout(() => {
                    window.location.href = '/admin/login';
                }, 2000);
            } else {
                showMessage(data.message || 'Произошла ошибка. Возможно, токен истёк.', 'danger');
            }
        } catch (error) {
            showMessage('Ошибка соединения с сервером', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Установить пароль';
        }
    });

    function showMessage(text, type) {
        messageDiv.textContent = text;
        messageDiv.className = `alert alert-${type}`;
        messageDiv.classList.remove('d-none');
    }

    function hideMessage() {
        messageDiv.classList.add('d-none');
    }
});

