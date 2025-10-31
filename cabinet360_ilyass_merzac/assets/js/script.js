/**
 * Cabinet360 - Main JavaScript
 * Global functions and utilities
 */

$(document).ready(function() {
    
    // Sidebar Toggle for Mobile
    $('#sidebarToggle').on('click', function() {
        $('#sidebar').toggleClass('show');
    });
    
    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('#sidebar, #sidebarToggle').length) {
                $('#sidebar').removeClass('show');
            }
        }
    });
    
    // Global Search Functionality
    let searchTimeout;
    $('#globalSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        if (query.length < 2) {
            $('#searchResults').html('<p class="text-muted text-center">Entrez au moins 2 caractères...</p>');
            return;
        }
        
        $('#searchResults').html('<div class="text-center"><div class="spinner-border text-gold" role="status"></div></div>');
        
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: window.location.origin + '/Cabinet360/actions/global_search.php',
                method: 'GET',
                data: { query: query },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displaySearchResults(response.results);
                    } else {
                        $('#searchResults').html('<p class="text-danger text-center">Erreur de recherche</p>');
                    }
                },
                error: function() {
                    $('#searchResults').html('<p class="text-danger text-center">Erreur de connexion</p>');
                }
            });
        }, 500);
    });
    
    function displaySearchResults(results) {
        if (!results.clients.length && !results.cases.length && !results.appointments.length) {
            $('#searchResults').html('<p class="text-muted text-center">Aucun résultat trouvé</p>');
            return;
        }
        
        let html = '';
        
        // Clients
        if (results.clients.length > 0) {
            html += '<h6 class="text-gold mt-3"><i class="fas fa-users"></i> Clients</h6>';
            results.clients.forEach(client => {
                html += `
                    <div class="search-result-item clickable-result" data-type="client" data-id="${client.id}" style="cursor: pointer;">
                        <strong>${client.full_name}</strong><br>
                        <small class="text-muted">CIN: ${client.cin} | Tél: ${client.phone || 'N/A'}</small>
                    </div>
                `;
            });
        }
        
        // Cases
        if (results.cases.length > 0) {
            html += '<h6 class="text-gold mt-3"><i class="fas fa-briefcase"></i> Dossiers</h6>';
            results.cases.forEach(c => {
                html += `
                    <div class="search-result-item">
                        <strong>${c.case_number}</strong> - ${c.client_name}<br>
                        <small class="text-muted">Type: ${c.type} | Statut: ${c.status}</small>
                    </div>
                `;
            });
        }
        
        // Appointments
        if (results.appointments.length > 0) {
            html += '<h6 class="text-gold mt-3"><i class="fas fa-calendar"></i> Rendez-vous</h6>';
            results.appointments.forEach(apt => {
                html += `
                    <div class="search-result-item">
                        <strong>${apt.client_name}</strong><br>
                        <small class="text-muted">${new Date(apt.date).toLocaleDateString('fr-FR')} à ${apt.time}</small>
                    </div>
                `;
            });
        }
        
        $('#searchResults').html(html);
        
        // Add click handlers for search results
        $('.clickable-result').on('click', function() {
            const type = $(this).data('type');
            const id = $(this).data('id');
            
            if (type === 'client') {
                window.location.href = window.location.origin + '/Cabinet360/pages/client_detail.php?id=' + id;
            }
            // Add more types as needed (cases, appointments, etc.)
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
    
    // Confirm before leaving page with unsaved changes
    $('form').on('change', 'input, select, textarea', function() {
        $(this).closest('form').data('changed', true);
    });
    
    $('form').on('submit', function() {
        $(this).data('changed', false);
    });
    
    // Form validation helpers
    window.validateForm = function(formId) {
        const form = document.getElementById(formId);
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }
        return true;
    };
    
    // Number formatting
    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('fr-MA', {
            style: 'currency',
            currency: 'MAD'
        }).format(amount);
    };
    
    // Date formatting
    window.formatDate = function(dateString) {
        return new Date(dateString).toLocaleDateString('fr-FR');
    };
    
    // Toast notification helper
    window.showToast = function(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const icon = type === 'success' ? 'fa-check-circle' : 
                    type === 'error' ? 'fa-exclamation-circle' : 
                    type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        
        const toast = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                <i class="fas ${icon}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(function() {
            toast.fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    };
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Print function
    window.printElement = function(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Impression</title>');
            printWindow.document.write('<style>body{font-family:Arial,sans-serif;}</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(element.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }
    };
    
    // Export table to CSV
    window.exportTableToCSV = function(tableId, filename = 'export.csv') {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                row.push(cols[j].innerText);
            }
            
            csv.push(row.join(','));
        }
        
        const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        const downloadLink = document.createElement('a');
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    };
    
    // Smooth scroll to top
    window.scrollToTop = function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    
    // Add scroll to top button
    $(window).scroll(function() {
        if ($(this).scrollTop() > 200) {
            if ($('#scrollTopBtn').length === 0) {
                $('body').append(`
                    <button id="scrollTopBtn" class="btn btn-primary" 
                            style="position: fixed; bottom: 30px; right: 30px; z-index: 999; border-radius: 50%; width: 50px; height: 50px;"
                            onclick="scrollToTop()">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                `);
            }
        } else {
            $('#scrollTopBtn').remove();
        }
    });
    
    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            $('#searchModal').modal('show');
            setTimeout(() => $('#globalSearch').focus(), 300);
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            $('.modal').modal('hide');
        }
    });
    
    console.log('%cCabinet360', 'font-size: 30px; color: #D4AF37; font-weight: bold;');
    console.log('%cSystème de Gestion pour Cabinet d\'Avocat', 'font-size: 14px; color: #999;');
    console.log('%c© 2025 Cabinet360 - Tous droits réservés', 'font-size: 12px; color: #666;');
    
});

