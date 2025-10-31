/**
 * Theme management functionality
 */
class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        // Apply theme on load
        this.applyTheme(this.theme);
        
        // Add event listener for theme toggle
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            this.toggleTheme();
        });
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Update theme in database if user is logged in
        if (typeof updateUserPreference === 'function') {
            updateUserPreference('theme', theme);
        }
        
        // Update toggle button text/icon if it exists
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.innerHTML = theme === 'dark' ? 
                '<i class="fas fa-sun"></i> Thème Clair' : 
                '<i class="fas fa-moon"></i> Thème Sombre';
        }
    }

    toggleTheme() {
        const newTheme = this.theme === 'dark' ? 'light' : 'dark';
        this.theme = newTheme;
        this.applyTheme(newTheme);
    }
}

// API function to update user preferences
function updateUserPreference(key, value) {
    fetch('../actions/update_preferences.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            key: key,
            value: value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Failed to update preference:', data.message);
        }
    })
    .catch(error => {
        console.error('Error updating preference:', error);
    });
}

// Initialize theme manager
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});