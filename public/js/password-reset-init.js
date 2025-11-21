document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('password-reset-form');
    const emailInput = document.getElementById('email');
    const submitBtn = document.getElementById('submit-btn');
    const messageDiv = document.getElementById('message');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = emailInput.value.trim();

        if (!email) {
            showMessage('Пожалуйста, введите email', 'danger');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Отправка...';
        hideMessage();

        try {
            const response = await fetch('/api/auth/password-reset/init', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email }),
            });

            const data = await response.json();

            if (response.ok) {
                showMessage('Если пользователь с таким email существует, на него отправлено письмо с инструкциями', 'success');
                form.reset();
            } else {
                showMessage(data.message || 'Произошла ошибка', 'danger');
            }
        } catch (error) {
            showMessage('Ошибка соединения с сервером', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Отправить';
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

