/**
 * Cabinet360 - Cases JavaScript
 */

$(document).ready(function() {
    
    // Add Case Form
    $('#addCaseForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'add');
        
        $.ajax({
            url: '../actions/case_actions.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Erreur lors de l\'ajout du dossier');
            }
        });
    });
    
    // Edit Case Button
    $('.edit-case').on('click', function() {
        const caseId = $(this).data('id');
        
        $.ajax({
            url: '../actions/case_actions.php',
            method: 'GET',
            data: { action: 'get', id: caseId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#edit_case_id').val(response.case.id);
                    $('#edit_case_number').val(response.case.case_number);
                    $('#edit_client_id').val(response.case.client_id);
                    $('#edit_description').val(response.case.description || '');
                    $('#edit_type').val(response.case.type);
                    $('#edit_status').val(response.case.status);
                    $('#edit_lawyer').val(response.case.lawyer);
                    $('#edit_date_opened').val(response.case.date_opened);
                    $('#edit_notes').val(response.case.notes);
                    
                    if (response.case.document_path) {
                        $('#current_document').html('<i class="fas fa-file"></i> Document actuel: ' + response.case.document_path.split('/').pop());
                    } else {
                        $('#current_document').html('Aucun document');
                    }
                    
                    $('#editCaseModal').modal('show');
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Erreur lors du chargement des données');
            }
        });
    });
    
    // Update Case Form
    $('#editCaseForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'update');
        
        $.ajax({
            url: '../actions/case_actions.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
    
    // View Case Button
    $('.view-case').on('click', function() {
        const caseId = $(this).data('id');
        
        $('#viewCaseModal').modal('show');
        
        $.ajax({
            url: '../actions/case_actions.php',
            method: 'GET',
            data: { action: 'get', id: caseId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const c = response.case;
                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-gold">Informations du Dossier</h6>
                                <p><strong>N° Dossier:</strong> ${c.case_number}</p>
                                <p><strong>Client:</strong> ${c.client_name}</p>
                                <p><strong>Type:</strong> <span class="badge bg-info">${c.type}</span></p>
                                <p><strong>Statut:</strong> <span class="badge bg-success">${c.status}</span></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-gold">Détails Supplémentaires</h6>
                                <p><strong>Avocat:</strong> ${c.lawyer || 'Non assigné'}</p>
                                <p><strong>Date d'ouverture:</strong> ${new Date(c.date_opened).toLocaleDateString('fr-FR')}</p>
                                <p><strong>Créé le:</strong> ${new Date(c.created_at).toLocaleDateString('fr-FR')}</p>
                            </div>
                        </div>
                        <hr>
                    `;
                    
                    if (c.document_path) {
                        html += `
                            <div class="mb-3">
                                <h6 class="text-gold">Document</h6>
                                <a href="../${c.document_path}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-file-download"></i> Télécharger le document
                                </a>
                            </div>
                        `;
                    }
                    
                    html += `
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-gold">Notes</h6>
                                <p>${c.notes || 'Aucune note'}</p>
                            </div>
                        </div>
                    `;
                    
                    $('#caseDetails').html(html);
                } else {
                    $('#caseDetails').html('<p class="text-danger">Erreur de chargement</p>');
                }
            },
            error: function() {
                $('#caseDetails').html('<p class="text-danger">Erreur de connexion</p>');
            }
        });
    });
    
    // Delete Case Button
    $('.delete-case').on('click', function() {
        const caseId = $(this).data('id');
        const caseNumber = $(this).data('number');
        
        if (confirm(`Êtes-vous sûr de vouloir supprimer le dossier ${caseNumber} ?\n\nCette action est irréversible.`)) {
            $.ajax({
                url: '../actions/case_actions.php',
                method: 'POST',
                data: { action: 'delete', case_id: caseId },
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

