document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    const alertContainer = document.getElementById('alert-container');

    const token = localStorage.getItem('jwt_token');
    if (token) {
        window.location.href = '/admin';
        return;
    }

    const showAlert = (message, type = 'danger') => {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    };

    const clearValidation = () => {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    };

    const setLoading = (loading) => {
        submitBtn.disabled = loading;
        submitText.classList.toggle('d-none', loading);
        submitSpinner.classList.toggle('d-none', !loading);
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearValidation();
        alertContainer.innerHTML = '';

        const formData = {
            email: emailInput.value,
            password: passwordInput.value,
        };

        setLoading(true);

        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error('Неверный email или пароль');
                }

                throw new Error(data.message || 'Ошибка входа');
            }

            localStorage.setItem('jwt_token', data.token);
            localStorage.setItem('user_data', JSON.stringify(data.user));

            window.location.href = '/admin';
        } catch (error) {
            showAlert(error.message, 'danger');
        } finally {
            setLoading(false);
        }
    });
});

