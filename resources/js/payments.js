document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.mark-paid').forEach(button => {
        button.addEventListener('click', function() {
            const paymentId = this.dataset.paymentId;
            const card = this.closest('.card');

            fetch(`/payments/${paymentId}/mark-paid`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                card.style.transition = 'opacity 300ms';
                card.style.opacity = '0';

                setTimeout(() => {
                    card.remove();
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Payment marked as paid');
                    } else {
                        alert('Payment marked as paid');
                    }
                }, 300);
            })
            .catch(error => {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to update payment');
                } else {
                    alert('Failed to update payment');
                }
                console.error('Error:', error);
            });
        });
    });
});


// airtime renewal


