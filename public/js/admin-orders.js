document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('jwt_token');
    
    if (!token) {
        window.location.href = '/admin/login';
        return;
    }

    const loading = document.getElementById('loading');
    const ordersContainer = document.getElementById('orders-container');
    const ordersTableBody = document.getElementById('orders-table-body');
    const noOrders = document.getElementById('no-orders');
    const alertContainer = document.getElementById('alert-container');
    const logoutBtn = document.getElementById('logout-btn');

    const showAlert = (message, type = 'danger') => {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    };

    const formatPrice = (amount, currency) => {
        const rubles = (amount / 100).toFixed(2);
        return `${rubles} ${currency}`;
    };

    const getStatusBadge = (status) => {
        const badges = {
            'pending': 'bg-warning',
            'completed': 'bg-success',
            'cancelled': 'bg-danger',
        };
        const badge = badges[status] || 'bg-secondary';
        const labels = {
            'pending': 'В ожидании',
            'completed': 'Завершен',
            'cancelled': 'Отменен',
        };
        const label = labels[status] || status;
        return `<span class="badge ${badge}">${label}</span>`;
    };

    const loadOrders = async () => {
        try {
            const response = await fetch('/api/orders', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            });

            if (response.status === 401) {
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user_data');
                window.location.href = '/admin/login';
                return;
            }

            if (!response.ok) {
                throw new Error('Не удалось загрузить заказы');
            }

            const orders = await response.json();

            loading.classList.add('d-none');
            ordersContainer.classList.remove('d-none');

            if (orders.length === 0) {
                noOrders.classList.remove('d-none');
                return;
            }

            orders.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${order.orderNumber}</td>
                    <td>${order.service.name}</td>
                    <td>${order.customerEmail}</td>
                    <td>${formatPrice(order.price.amount, order.price.currency)}</td>
                    <td>${getStatusBadge(order.status)}</td>
                    <td>${order.createdAt}</td>
                `;
                ordersTableBody.appendChild(row);
            });
        } catch (error) {
            loading.classList.add('d-none');
            ordersContainer.classList.remove('d-none');
            showAlert(error.message, 'danger');
        }
    };

    logoutBtn.addEventListener('click', () => {
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('user_data');
        window.location.href = '/admin/login';
    });

    loadOrders();
});

