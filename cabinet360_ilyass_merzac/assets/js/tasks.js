/**
 * Cabinet360 - Tasks JavaScript
 */

$(document).ready(function() {
    
    // Add Task Form
    $('#addTaskForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '../actions/task_actions.php',
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
                alert('Erreur lors de l\'ajout de la tâche');
            }
        });
    });
    
    // Toggle Task Status (checkbox)
    window.toggleTaskStatus = function(taskId, completed) {
        const newStatus = completed ? 'terminée' : 'à_faire';
        
        $.ajax({
            url: '../actions/task_actions.php',
            method: 'POST',
            data: { 
                action: 'update_status', 
                task_id: taskId, 
                status: newStatus 
            },
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
    };
    
    // Make task rows clickable
    $('table tbody tr').on('click', function(e) {
        // Don't trigger if clicking on action buttons or checkbox
        if (!$(e.target).closest('.action-buttons').length && !$(e.target).closest('.form-check-input').length) {
            const taskId = $(this).find('.view-task').data('id');
            if (taskId) {
                openTaskModal(taskId);
            }
        }
    });
    
    // Add cursor pointer to rows
    $('table tbody tr').css('cursor', 'pointer');
    
    // View Task Button
    $('.view-task').on('click', function(e) {
        e.stopPropagation(); // Prevent row click
        const taskId = $(this).data('id');
        openTaskModal(taskId);
    });
    
    // Function to open task modal
    function openTaskModal(taskId) {
        
        $('#viewTaskModal').modal('show');
        $('#taskDetailsContent').html('<div class="text-center"><div class="spinner-border text-gold"></div></div>');
        
        $.ajax({
            url: '../actions/task_actions.php',
            method: 'GET',
            data: { action: 'get', id: taskId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const task = response.task;
                    
                    // Status colors
                    const statusColors = {
                        'à_faire': 'secondary',
                        'en_cours': 'warning',
                        'terminée': 'success',
                        'annulée': 'dark'
                    };
                    
                    const priorityColors = {
                        'urgente': 'danger',
                        'haute': 'warning',
                        'moyenne': 'info',
                        'basse': 'secondary'
                    };
                    
                    const isCompleted = task.status === 'terminée';
                    
                    // Format status display
                    let statusDisplay = task.status ? task.status.replace(/_/g, ' ') : 'à faire';
                    statusDisplay = statusDisplay.charAt(0).toUpperCase() + statusDisplay.slice(1);
                    
                    // Format priority display
                    let priorityDisplay = task.priority ? task.priority : 'moyenne';
                    priorityDisplay = priorityDisplay.charAt(0).toUpperCase() + priorityDisplay.slice(1);
                    
                    let html = `
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h3 class="text-gold"><i class="fas fa-tasks"></i> ${task.title}</h3>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-flag text-gold"></i> Priorité:</strong><br>
                                    <span class="badge bg-${priorityColors[task.priority] || 'secondary'}">${priorityDisplay}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-info-circle text-gold"></i> Statut:</strong><br>
                                    <span class="badge bg-${statusColors[task.status] || 'secondary'}">${statusDisplay}</span>
                                </p>
                            </div>
                        </div>
                        
                        ${task.description ? `
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <p><strong><i class="fas fa-align-left text-gold"></i> Description:</strong></p>
                                <div class="alert alert-info">${task.description}</div>
                            </div>
                        </div>
                        ` : ''}
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-briefcase text-gold"></i> Dossier:</strong> 
                                    ${task.case_number || '<em class="text-muted">Non lié</em>'}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-calendar text-gold"></i> Échéance:</strong> 
                                    ${task.due_date ? new Date(task.due_date).toLocaleDateString('fr-FR') : '<em class="text-muted">Non définie</em>'}
                                </p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-user text-gold"></i> Assigné à:</strong> ${task.assigned_name || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-clock text-gold"></i> Créé le:</strong> ${new Date(task.created_at).toLocaleDateString('fr-FR')}</p>
                            </div>
                        </div>
                        
                        ${task.completed_at ? `
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <p><strong><i class="fas fa-check-circle text-success"></i> Terminé le:</strong> ${new Date(task.completed_at).toLocaleDateString('fr-FR')} à ${new Date(task.completed_at).toLocaleTimeString('fr-FR')}</p>
                            </div>
                        </div>
                        ` : ''}
                        
                        <hr style="border-color: rgba(212, 175, 55, 0.3);">
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="taskCompletedSwitch" 
                                           ${isCompleted ? 'checked' : ''} 
                                           onchange="updateTaskStatusFromModal(${task.id}, this.checked)">
                                    <label class="form-check-label" for="taskCompletedSwitch">
                                        <strong style="font-size: 1.1rem;">
                                            ${isCompleted ? '<i class="fas fa-check-circle text-success"></i> Tâche terminée' : '<i class="fas fa-hourglass-half text-warning"></i> Marquer comme terminée'}
                                        </strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#taskDetailsContent').html(html);
                } else {
                    $('#taskDetailsContent').html('<div class="alert alert-danger">Erreur de chargement</div>');
                }
            },
            error: function() {
                $('#taskDetailsContent').html('<div class="alert alert-danger">Erreur de connexion</div>');
            }
        });
    }
    
    // Update task status from modal
    window.updateTaskStatusFromModal = function(taskId, completed) {
        const newStatus = completed ? 'terminée' : 'à_faire';
        
        $.ajax({
            url: '../actions/task_actions.php',
            method: 'POST',
            data: { 
                action: 'update_status', 
                task_id: taskId, 
                status: newStatus 
            },
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
    };
    
    // Edit Task Button
    $('.edit-task').on('click', function() {
        const taskId = $(this).data('id');
        alert('Fonctionnalité d\'édition en développement. ID de la tâche: ' + taskId);
        // TODO: Implement edit task functionality
    });
    
    // Delete Task
    $('.delete-task').on('click', function() {
        const taskId = $(this).data('id');
        
        if (confirm('Êtes-vous sûr de vouloir supprimer cette tâche?')) {
            $.ajax({
                url: '../actions/task_actions.php',
                method: 'POST',
                data: { action: 'delete', task_id: taskId },
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

