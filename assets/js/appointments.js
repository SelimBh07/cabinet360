/**
 * Cabinet360 - Appointments JavaScript
 */

$(document).ready(function() {
    
    let calendar;
    
    // Initialize FullCalendar
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 'auto',
            events: function(info, successCallback, failureCallback) {
                $.ajax({
                    url: '../actions/appointment_actions.php',
                    method: 'GET',
                    data: { action: 'get_calendar' },
                    dataType: 'json',
                    success: function(events) {
                        successCallback(events);
                    },
                    error: function() {
                        failureCallback();
                    }
                });
            },
            eventClick: function(info) {
                const appointmentId = info.event.id;
                viewAppointment(appointmentId);
            },
            eventColor: '#D4AF37'
        });
        calendar.render();
    }
    
    // View Toggle
    $('#btnCalendarView').on('click', function() {
        $('#calendarView').show();
        $('#listView').hide();
        $(this).addClass('active');
        $('#btnListView').removeClass('active');
    });
    
    $('#btnListView').on('click', function() {
        $('#calendarView').hide();
        $('#listView').show();
        $(this).addClass('active');
        $('#btnCalendarView').removeClass('active');
        loadAppointmentsList();
    });
    
    // Load Appointments List
    function loadAppointmentsList() {
        $.ajax({
            url: '../actions/appointment_actions.php',
            method: 'GET',
            data: { action: 'get_all' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let html = '';
                    response.appointments.forEach(apt => {
                        const statusBadge = apt.status === 'planifie' ? 'bg-warning' : 
                                          (apt.status === 'termine' ? 'bg-success' : 'bg-danger');
                        
                        html += `
                            <tr>
                                <td>${new Date(apt.date).toLocaleDateString('fr-FR')}</td>
                                <td>${apt.time}</td>
                                <td>${apt.client_name}</td>
                                <td>${apt.purpose || '-'}</td>
                                <td>${apt.location || '-'}</td>
                                <td><span class="badge ${statusBadge}">${apt.status}</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-info view-appointment" data-id="${apt.id}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning edit-appointment" data-id="${apt.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-appointment" data-id="${apt.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    
                    $('#appointmentsTable tbody').html(html || '<tr><td colspan="7" class="text-center">Aucun rendez-vous</td></tr>');
                    
                    // Reattach event handlers
                    attachListEventHandlers();
                }
            }
        });
    }
    
    // Attach event handlers for dynamically loaded buttons
    function attachListEventHandlers() {
        $('.view-appointment').off('click').on('click', function() {
            viewAppointment($(this).data('id'));
        });
        
        $('.edit-appointment').off('click').on('click', function() {
            editAppointment($(this).data('id'));
        });
        
        $('.delete-appointment').off('click').on('click', function() {
            deleteAppointment($(this).data('id'));
        });
    }
    
    // Add Appointment Form
    $('#addAppointmentForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '../actions/appointment_actions.php',
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
                alert('Erreur lors de l\'ajout du rendez-vous');
            }
        });
    });
    
    // Edit Appointment
    function editAppointment(appointmentId) {
        $.ajax({
            url: '../actions/appointment_actions.php',
            method: 'GET',
            data: { action: 'get', id: appointmentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#edit_appointment_id').val(response.appointment.id);
                    $('#edit_client_id').val(response.appointment.client_id);
                    $('#edit_date').val(response.appointment.date);
                    $('#edit_time').val(response.appointment.time);
                    $('#edit_purpose').val(response.appointment.purpose);
                    $('#edit_location').val(response.appointment.location);
                    $('#edit_status').val(response.appointment.status);
                    
                    $('#editAppointmentModal').modal('show');
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Erreur lors du chargement des données');
            }
        });
    }
    
    // Update Appointment Form
    $('#editAppointmentForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '../actions/appointment_actions.php',
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
    
    // View Appointment
    function viewAppointment(appointmentId) {
        $('#viewAppointmentModal').modal('show');
        
        $.ajax({
            url: '../actions/appointment_actions.php',
            method: 'GET',
            data: { action: 'get', id: appointmentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const apt = response.appointment;
                    const statusBadge = apt.status === 'planifie' ? 'bg-warning' : 
                                      (apt.status === 'termine' ? 'bg-success' : 'bg-danger');
                    
                    let html = `
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-gold">Informations du Rendez-vous</h6>
                                <p><strong>Client:</strong> ${apt.client_name}</p>
                                <p><strong>Date:</strong> ${new Date(apt.date).toLocaleDateString('fr-FR')}</p>
                                <p><strong>Heure:</strong> ${apt.time}</p>
                                <p><strong>Objet:</strong> ${apt.purpose || 'Non spécifié'}</p>
                                <p><strong>Lieu:</strong> ${apt.location || 'Non spécifié'}</p>
                                <p><strong>Statut:</strong> <span class="badge ${statusBadge}">${apt.status}</span></p>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-warning edit-apt-btn" data-id="${apt.id}">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn btn-danger delete-apt-btn" data-id="${apt.id}">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    `;
                    
                    $('#appointmentDetails').html(html);
                    
                    // Attach buttons
                    $('.edit-apt-btn').on('click', function() {
                        $('#viewAppointmentModal').modal('hide');
                        editAppointment($(this).data('id'));
                    });
                    
                    $('.delete-apt-btn').on('click', function() {
                        $('#viewAppointmentModal').modal('hide');
                        deleteAppointment($(this).data('id'));
                    });
                } else {
                    $('#appointmentDetails').html('<p class="text-danger">Erreur de chargement</p>');
                }
            },
            error: function() {
                $('#appointmentDetails').html('<p class="text-danger">Erreur de connexion</p>');
            }
        });
    }
    
    // Delete Appointment
    function deleteAppointment(appointmentId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous ?')) {
            $.ajax({
                url: '../actions/appointment_actions.php',
                method: 'POST',
                data: { action: 'delete', appointment_id: appointmentId },
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
    }
    
});

