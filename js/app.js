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

// Settings Mobile menu logic
var settingsMobileMenuIcon = document.getElementById('settingsMobileMenuIcon');
var settingsMenu = document.getElementById('settingsMenu');
if(settingsMobileMenuIcon && settingsMenu) {
    // Open mobile menu
    settingsMobileMenuIcon.addEventListener('click', () => {
        settingsMenu.classList.toggle('active');
        settingsMobileMenuIcon.classList.toggle('active');
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

// Sometimes, PhP takes time to do stuff. Add a loading sign!
var loadingBtns = document.querySelectorAll('.trigger-loading');
var limsTimeout = document.querySelector('#php2js').dataset.limsTimeout;
loadingBtns.forEach((btn) => {
    // Listen to if a button which requires some loading is clicked
    btn.addEventListener('click', () => {          
        // Get the button's text content
        var textContent = btn.textContent;

        // Add loading class and text content to the button
        btn.classList.add('loading');
        btn.textContent = 'Loading...';

        // Add downloading to the button if something is downloading
        if(btn.classList.contains('downloadable')) {
            btn.textContent = "Downloading...";
        }

        // If the button is indeed a button (check necessary to sift out input checks)
        if(btn.nodeName === 'BUTTON') {
            // Get the button's parent and check it's a form!
            var form = btn.parentElement;
            if(form.nodeName === 'FORM') {
                // Disable the button if we've submitted!
                form.addEventListener('submit', () => {
                    btn.disabled = true;
                });


                // Get all required fields
                var children = form.querySelectorAll('input[required]');

                // Function to return if the required fields are all satisfied
                var findEmpty = Array.from(children).find((element) => {
                    if(element.value.length < 1) {
                        return true;
                    }
                    return false;
                });

                // If they are not, remove the loading and replace with the original button text content
                if(findEmpty) {
                    btn.textContent = textContent;
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            }
        }

        // If nothing has happened for X seconds (LIMS timeout), reset the button as we've probably done something wrong.
        setTimeout(() => {
            btn.textContent = textContent;
            btn.classList.remove('loading');
            btn.disabled = false;
        }, limsTimeout * 1000);
    });
});

var yanaButton = document.querySelector("#yanabutton");
var yanaDemon = document.getElementById("yanademon");

yanaButton.addEventListener("click", () => {
    yanaDemon.classList.toggle("active");
});