/**
 * Cabinet360 - Payment Management JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add Payment Form
    const addPaymentForm = document.getElementById('addPaymentForm');
    if (addPaymentForm) {
        addPaymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add');
            
            fetch('../actions/payment_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    addPaymentForm.reset();
                    bootstrap.Modal.getInstance(document.getElementById('addPaymentModal')).hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Erreur lors de l\'enregistrement');
            });
        });
    }
    
    // Edit Payment Form
    const editPaymentForm = document.getElementById('editPaymentForm');
    if (editPaymentForm) {
        editPaymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update');
            
            fetch('../actions/payment_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    bootstrap.Modal.getInstance(document.getElementById('editPaymentModal')).hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Erreur lors de la mise à jour');
            });
        });
    }
    
    // View Payment Buttons
    document.querySelectorAll('.view-payment').forEach(button => {
        button.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-id');
            viewPayment(paymentId);
        });
    });
    
    // Edit Payment Buttons
    document.querySelectorAll('.edit-payment').forEach(button => {
        button.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-id');
            editPayment(paymentId);
        });
    });
    
    // Delete Payment Buttons
    document.querySelectorAll('.delete-payment').forEach(button => {
        button.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-id');
            deletePayment(paymentId);
        });
    });
    
    // Set default date to today
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            input.value = new Date().toISOString().split('T')[0];
        }
    });
});

function viewPayment(paymentId) {
    fetch(`../actions/payment_actions.php?action=get&id=${paymentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const payment = data.payment;
                const modalBody = document.getElementById('paymentDetails');
                
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-user"></i> Client</h6>
                            <p>${payment.client_name}</p>
                            
                            <h6><i class="fas fa-briefcase"></i> Dossier</h6>
                            <p>${payment.case_number || 'Aucun dossier spécifique'}</p>
                            
                            <h6><i class="fas fa-calendar"></i> Date</h6>
                            <p>${formatDate(payment.date)}</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-money-bill-wave"></i> Montant</h6>
                            <p class="h4 text-gold">${formatAmount(payment.amount)} MAD</p>
                            
                            <h6><i class="fas fa-credit-card"></i> Méthode</h6>
                            <p><span class="badge bg-info">${payment.method}</span></p>
                            
                            <h6><i class="fas fa-info-circle"></i> Statut</h6>
                            <p><span class="badge ${getStatusClass(payment.status)}">${payment.status}</span></p>
                        </div>
                    </div>
                    ${payment.notes ? `
                        <hr>
                        <h6><i class="fas fa-sticky-note"></i> Notes</h6>
                        <p>${payment.notes}</p>
                    ` : ''}
                `;
                
                new bootstrap.Modal(document.getElementById('viewPaymentModal')).show();
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Erreur lors du chargement');
        });
}

function editPayment(paymentId) {
    fetch(`../actions/payment_actions.php?action=get&id=${paymentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const payment = data.payment;
                
                // Fill form fields
                document.getElementById('edit_payment_id').value = payment.id;
                document.getElementById('edit_client_id').value = payment.client_id;
                document.getElementById('edit_case_id').value = payment.case_id || '';
                document.getElementById('edit_date').value = payment.date;
                document.getElementById('edit_amount').value = payment.amount;
                document.getElementById('edit_method').value = payment.method;
                document.getElementById('edit_status').value = payment.status;
                document.getElementById('edit_notes').value = payment.notes || '';
                
                new bootstrap.Modal(document.getElementById('editPaymentModal')).show();
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Erreur lors du chargement');
        });
}

function deletePayment(paymentId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('payment_id', paymentId);
        
        fetch('../actions/payment_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Erreur lors de la suppression');
        });
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

function formatAmount(amount) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

function getStatusClass(status) {
    const classes = {
        'payé': 'bg-success',
        'impayé': 'bg-danger',
        'partiel': 'bg-warning'
    };
    return classes[status] || 'bg-secondary';
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the page
    const container = document.querySelector('.content-area');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}