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

// In the settings menu, choose whether to show LIMS options
var protocolDropdown = document.getElementById("protocol");
var hl7Section = document.getElementById("hl7Options");
var cainSection = document.getElementById("cainOptions");

if(protocolDropdown && hl7Section && cainSection) {
    protocolDropdown.addEventListener('change', () => {
        if(protocolDropdown.value == 1) {
            // If the selected protocol is HL7, no need to show or require the LIMS settings
            hl7Section.classList.add("active");
            cainSection.classList.remove("active");
        } else {
            hl7Section.classList.remove("active");
            cainSection.classList.add("active");
        }
    });
}

// We need to be able to pop open test resuults and display all the results details
var resultsTable = document.getElementById('resultsTable');

if(resultsTable) {
    var resultRows = document.querySelectorAll('tr.result');
    resultRows.forEach((result) => {
        
        result.addEventListener('click', () => {
            var resultId = result.id;

            var id = resultId.replace("result", "");

            // Get the result's corresponding modal
            var resultModal = document.getElementById(resultId + "Modal");

            // Show it!
            resultModal.classList.add('active');

            // Hide it!
            resultModal.querySelector('.result-modal-backdrop').addEventListener('click', (e) => {
                // Get the result details
                var resultDetails = resultModal.querySelector('.result-details');
                
                if(!resultDetails.contains(e.target) || e.target.classList.contains('modal-close')) {
                    resultModal.classList.remove('active');
                }
            })

            // Check if we have a graph already
            var canvas = document.getElementById(`${id}Canvas`);

            if(!canvas) {
                // Load a graph (if it is not already loaded)
                var phpFileUrl = '/scripts/graph-check.php';
                
                // Create a new XMLHttpRequest object
                var xhr = new XMLHttpRequest();
    
                // Open a GET request to the PHP file
                xhr.open('POST', phpFileUrl, true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    
                // When the file is ready
                xhr.onreadystatechange = function() {
                    // If the file has been successfully opened, watch it for progress
                    if (xhr.readyState === 4) {
                        // Could add a loading state here (if necessary)
    
                        // If we got a valid response, interpret it
                        if (xhr.status === 200) {
                            // The response code/message
                            var data = JSON.parse(xhr.responseText);
                            var resultDetailsSection = resultModal.querySelector('.result-details');
    
                            // If we have an error in our JSON, display the error.
                            if(data.error) {
                                // Render an error div with relevant information.
                                var errorDiv = document.createElement('div');
                                errorDiv.id = id + "Canvas";
                                errorDiv.classList.add("error");

                                errorDiv.innerHTML = `
                                    <p>No Graph Data Found.</p>
                                `;

                                resultDetailsSection.appendChild(errorDiv);
                            } else {
                                var canvasWrapper = document.createElement('div');
                                canvasWrapper.classList.add('canvas-wrapper');

                                // Create a canvas
                                canvas = document.createElement('canvas');

                                // Build the graph in a canvas on the bottom of the result modal
                                canvas.id = id + "Canvas";

                                // Add it to the DOM
                                resultDetailsSection.appendChild(canvasWrapper);
                                canvasWrapper.appendChild(canvas);

                                // We need to know the number of cycles required.
                                var numCyclesRequired = 0;

                                // Create the datasets
                                var dataSets = [];
                                data.forEach((dataSet) => {
                                    // Shift the label off the array
                                    var label = dataSet.shift();

                                    // See how many points on the x axis we need
                                    numCyclesRequired = Math.max(dataSet.length, numCyclesRequired);
                                    dataSets.push({
                                        label: label.y,
                                        data: dataSet,
                                        borderWidth: 2,
                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                    });
                                });

                                const graphData = {
                                    datasets: dataSets
                                };

                                new Chart(canvas, {
                                    type: 'line',
                                    data: graphData,
                                    options: {
                                        responsive: false,
                                        maintainAspectRatio: false,
                                        plugins: {
                                          title: {
                                            display: true,
                                            text: 'Result Data',
                                            font: {
                                                size: 26,
                                                weight: 600,

                                            }
                                          },
                                          legend: {
                                            labels: {
                                                font: {
                                                    size: 15,
                                                    weight: 600
                                                }
                                            }
                                          }
                                        },
                                        responsive: true,
                                        interatction: {
                                            mode: 'index',
                                            intersect: false
                                        },
                                        scales: {
                                            y: {
                                                type: 'linear',
                                                display: true,
                                                title: {
                                                    display: true,
                                                    text: "Intensity",
                                                    font: {
                                                        size: 14
                                                    }
                                                },
                                            },
                                            x: {
                                                title: {
                                                    display: true,
                                                    text: "Cycles",
                                                    font: {
                                                        size: 14
                                                    }
                                                },
                                            }
                                        },
                                        elements: {
                                            line: {
                                                tension: 0.2
                                            }
                                        }
                                    }
                                });
                            }
                            
                        } else {
                            // Handle error
                            statusSpan.innerHTML = 'Error updating. Please refresh and try again.';
                        }
                    }
                };
    
                // Send the AJAX request
                xhr.send('id=' + encodeURIComponent(id));
            }
        });

    })
}

// Within user settings, we need to display the modal depending on the checked state of the time user out field
var userTimeoutCheckbox = document.getElementById('userTimeout');
var userTimeoutInput = document.getElementById('sessionTimeout');
var userTimeoutAmount = document.getElementById('userTimeoutAmount');

if(userTimeoutCheckbox && userTimeoutInput && userTimeoutAmount) {
    userTimeoutCheckbox.addEventListener('change', () => {
        if(userTimeoutCheckbox.checked) {
            userTimeoutAmount.classList.add('active');
            userTimeoutInput.value = 30;
        } else {
            userTimeoutAmount.classList.remove('active');
            userTimeoutInput.value = 0;
        }
    });

    userTimeoutInput.addEventListener('change', () => {
        if(userTimeoutInput.value == 0) {
            userTimeoutCheckbox.checked = false;
            userTimeoutAmount.classList.remove('active');
        } else {
            userTimeoutCheckbox.checked = true;
            userTimeoutAmount.classList.add('active');
        }
    });
}

// User modal triggers
var userModals = document.getElementById('usersTable');

if(userModals) {
    document.addEventListener('click', (e) => {
        var closestUser = null;
        var target = e.target;
        var userModals = document.querySelectorAll('.user-modal');

        userModals.forEach((modal) => {
            if(!modal.contains(e.target) || target.classList.contains('close-user-modal')) {
                modal.classList.remove('active');
            }
        });

        if(target.classList.contains('delete-user-button')) {
            userModals.forEach((modal) => {
                modal.classList.remove('active');
            });
            var operatorToDelete = target.dataset.operator;
            var operatorId = target.dataset.id;
            document.getElementById('operatorToDelete').innerHTML = operatorToDelete;
            document.querySelectorAll('.form-operator-id').forEach((formId) => {
                formId.value = operatorId;
            });
            document.getElementById('deleteUserModal').classList.add('active');
        }

        if(target.classList.contains('new-user-button')) {
            document.getElementById('newUserModal').classList.add('active');
        }
    
        while(target && !closestUser && !target.classList.contains('table-button')) {
            if(target.classList.contains('user')) {
                closestUser = target;
            } else {
                target = target.parentElement;
            }
        }
    
        if(closestUser) {
            document.getElementById(`${closestUser.id}Modal`).classList.add('active');
        }
    })
}