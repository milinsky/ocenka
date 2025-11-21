document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('order-form');
    const serviceSelect = document.getElementById('service');
    const emailInput = document.getElementById('email');
    const priceDisplay = document.getElementById('price-display');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    const alertContainer = document.getElementById('alert-container');

    let services = [];

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

    const loadServices = async () => {
        try {
            const response = await fetch('/api/services');
            
            if (!response.ok) {
                throw new Error('Не удалось загрузить список услуг');
            }

            services = await response.json();
            
            services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
                option.dataset.amount = service.price.amount;
                option.dataset.currency = service.price.currency;
                serviceSelect.appendChild(option);
            });
        } catch (error) {
            showAlert(error.message, 'danger');
        }
    };

    const formatPrice = (amount, currency) => {
        const rubles = (amount / 100).toFixed(2);
        return `${rubles} ${currency}`;
    };

    serviceSelect.addEventListener('change', () => {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        
        if (selectedOption.value) {
            const amount = selectedOption.dataset.amount;
            const currency = selectedOption.dataset.currency;
            priceDisplay.textContent = formatPrice(amount, currency);
        } else {
            priceDisplay.textContent = '-';
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearValidation();
        alertContainer.innerHTML = '';

        const formData = {
            serviceId: serviceSelect.value,
            customerEmail: emailInput.value,
        };

        setLoading(true);

        try {
            const response = await fetch('/api/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422 && data.violations) {
                    data.violations.forEach(violation => {
                        const field = violation.field === 'serviceId' ? serviceSelect : emailInput;
                        field.classList.add('is-invalid');
                        field.nextElementSibling.textContent = violation.message;
                    });
                    return;
                }

                throw new Error(data.message || 'Ошибка при создании заказа');
            }

            showAlert(`Заказ успешно создан! Номер заказа: ${data.orderNumber}`, 'success');
            form.reset();
            priceDisplay.textContent = '-';
        } catch (error) {
            showAlert(error.message, 'danger');
        } finally {
            setLoading(false);
        }
    });

    loadServices();
});

