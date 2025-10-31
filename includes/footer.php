            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo APP_URL; ?>/assets/js/script.js"></script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        // Register Service Worker for PWA functionality
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo APP_URL; ?>/service-worker.js')
                    .then((registration) => {
                        console.log('✓ Service Worker registered successfully:', registration.scope);
                        
                        // Check for updates periodically
                        setInterval(() => {
                            registration.update();
                        }, 60000); // Check every minute
                    })
                    .catch((error) => {
                        console.log('✗ Service Worker registration failed:', error);
                    });
            });
            
            // PWA Install Prompt
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (e) => {
                // Prevent the default prompt
                e.preventDefault();
                deferredPrompt = e;
                
                // Show custom install button/banner (optional)
                console.log('💡 PWA can be installed!');
                
                // You can show a custom UI here to prompt installation
                // For now, we'll just log it
                setTimeout(() => {
                    if (confirm('Installer Cabinet360 comme application mobile?')) {
                        deferredPrompt.prompt();
                        deferredPrompt.userChoice.then((choiceResult) => {
                            if (choiceResult.outcome === 'accepted') {
                                console.log('✓ PWA installation accepted');
                            } else {
                                console.log('✗ PWA installation declined');
                            }
                            deferredPrompt = null;
                        });
                    }
                }, 3000); // Show prompt after 3 seconds
            });
            
            // Detect if app is running as PWA
            window.addEventListener('appinstalled', () => {
                console.log('✓ PWA installed successfully!');
                deferredPrompt = null;
            });
            
            // Check if running as installed PWA
            if (window.matchMedia('(display-mode: standalone)').matches) {
                console.log('✓ Running as installed PWA');
            }
        }
    </script>
    
    <!-- Page-specific scripts -->
    <?php if (isset($page_scripts)): ?>
        <?php echo $page_scripts; ?>
    <?php endif; ?>
</body>
</html>

