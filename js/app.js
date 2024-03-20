// Mobile menu logic
var mobileMenuIcon = document.getElementById('menuIcon');
var mobileMenuCloseIcon = document.getElementById('mobMenuClose');
var mobileMenuWrapper = document.getElementById('mobileMenu');

if(mobileMenuCloseIcon && mobileMenuIcon && mobileMenuWrapper) {
    // Open mobile menu
    mobileMenuIcon.addEventListener('click', () => {
        mobileMenuWrapper.classList.add('active');
    });

    // Close mobile menu
    mobileMenuCloseIcon.addEventListener('click', () => {
        mobileMenuWrapper.classList.remove('active');
    });
}

// Logout modal logic
var logoutModal = document.getElementById('logoutModal');
var userIcons = document.querySelectorAll('.logout-trigger');
var cancelBtn = document.getElementById('logoutCancel');

if(logoutModal) {
    document.addEventListener('click', (e) => {
        if (logoutModal.classList.contains('active') && !logoutModal.contains(e.target)) {
            logoutModal.classList.remove('active');
        }
    });
    
    userIcons.forEach((icon) => {
        icon.addEventListener('click', (e) => {
            logoutModal.classList.add('active');
            // Stop event propagation to prevent immediate closing of modal
            e.stopPropagation();
        });
    });
    
    cancelBtn.addEventListener('click', () => {
        logoutModal.classList.remove('active');
    });
}