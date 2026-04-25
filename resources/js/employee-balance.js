
document.addEventListener('DOMContentLoaded', function() {
    const employeeSelect = document.getElementById('employee_id');
    const balanceDisplay = document.getElementById('current-balance-display');

    employeeSelect.addEventListener('change', function() {
        const selectedValue = this.value;

        if (!selectedValue) {
            balanceDisplay.innerHTML = '<span class="text-muted">Select an employee to see current balance</span>';
            return;
        }

        // Extract employee ID from the value (format: "id|name")
        const employeeId = selectedValue.split('|')[0];

        // Show loading state
        balanceDisplay.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading balance...</span>';

        // Fetch current balance using the route name
        fetch(`/deductions/get-balance/${employeeId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const balance = parseFloat(data.current_balance);
                    const balanceClass = balance < 0 ? 'text-danger' : (balance > 0 ? 'text-success' : 'text-muted');
                    const balanceText = balance < 0 ? `-$${Math.abs(balance).toFixed(2)}` : `$${balance.toFixed(2)}`;

                    balanceDisplay.innerHTML = `
                        <span class="${balanceClass} fw-bold">
                            ${balanceText}
                        </span>
                        ${balance < 0 ? '(Amount Owed)' : (balance > 0 ? '(Credit Balance)' : '(Zero Balance)')}
                    `;
                } else {
                    balanceDisplay.innerHTML = `<span class="text-danger">${data.message || 'Error loading balance'}</span>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                balanceDisplay.innerHTML = '<span class="text-danger">Error loading balance</span>';
            });
    });
});
