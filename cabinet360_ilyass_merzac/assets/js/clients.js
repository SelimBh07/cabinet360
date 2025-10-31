/**
 * Cabinet360 - Clients JavaScript
 */

$(document).ready(function() {
    
    // Make table rows clickable to view client details
    $('table tbody tr').on('click', function(e) {
        // Don't trigger if clicking on action buttons
        if (!$(e.target).closest('.action-buttons').length) {
            const clientId = $(this).find('.view-client').data('id');
            if (clientId) {
                window.location.href = 'client_detail.php?id=' + clientId;
            }
        }
    });
    
    // Add cursor pointer to table rows
    $('table tbody tr').css('cursor', 'pointer');
    
    // Add Client Form
    $('#addClientForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '../actions/client_actions.php',
            method: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Erreur lors de l\'ajout du client');
            }
        });
    });
    
    // Edit Client Button
    $('.edit-client').on('click', function() {
        const clientId = $(this).data('id');
        
        $.ajax({
            url: '../actions/client_actions.php',
            method: 'GET',
            data: { action: 'get', id: clientId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#edit_client_id').val(response.client.id);
                    $('#edit_full_name').val(response.client.full_name);
                    $('#edit_cin').val(response.client.cin);
                    $('#edit_phone').val(response.client.phone);
                    $('#edit_email').val(response.client.email);
                    $('#edit_address').val(response.client.address);
                    $('#edit_notes').val(response.client.notes);
                    
                    $('#editClientModal').modal('show');
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Erreur lors du chargement des données');
            }
        });
    });
    
    // Update Client Form
    $('#editClientForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '../actions/client_actions.php',
            method: 'POST',
            data: $(this).serialize() + '&action=update',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Erreur lors de la mise à jour');
            }
        });
    });
    
    // View Client Button - Navigate to detail page
    $('.view-client').on('click', function() {
        const clientId = $(this).data('id');
        window.location.href = 'client_detail.php?id=' + clientId;
    });
    
    // Delete Client Button
    $('.delete-client').on('click', function() {
        const clientId = $(this).data('id');
        const clientName = $(this).data('name');
        
        if (confirm(`Êtes-vous sûr de vouloir supprimer ${clientName} ?\n\nCela supprimera également tous les dossiers, rendez-vous et paiements associés.`)) {
            $.ajax({
                url: '../actions/client_actions.php',
                method: 'POST',
                data: { action: 'delete', client_id: clientId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('Erreur lors de la suppression');
                }
            });
        }
    });
    
});

