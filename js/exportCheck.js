// Get the delete button
var deleteBtn = document.getElementById('deleteBtn');
var dateRangePicker = document.getElementById('dateRangePicker');

document.getElementById('backupBtn').addEventListener('click', function() {
    // Create the update alert element
    var updateAlert = document.createElement('div');
    updateAlert.id = 'updateAlert';

    // Get the main element
    var mainElement = document.querySelector('main');

    // Insert the update alert as the first child of the main element
    mainElement.insertAdjacentElement('afterbegin', updateAlert);
    updateAlert.innerHTML = '<h2>Samba HUB is generating a backup...</h2><p>This process may take a while.</p>';

    // Create the container
    var container = document.createElement('div');
    container.className = 'container';

    // Create the loading spinner
    var loadingSpinner = document.createElement('div');
    loadingSpinner.className = 'loadingspinner';

    // Create the square elements
    for (var i = 1; i <= 5; i++) {
        var square = document.createElement('div');
        square.id = 'square' + i;
        loadingSpinner.appendChild(square);
    }

    // Append the loading spinner to the container
    container.appendChild(loadingSpinner);

    // Append the container to the body
    updateAlert.appendChild(container);

    // Set the document title
    document.title = "Backing up...";

    // Disable the button while processing
    this.disabled = true;

    // Export the data
    var phpFileUrl = '/data/export-check.php';

    // Create a new XMLHttpRequest object
    var xhr = new XMLHttpRequest();

    // Open a POST request to the PHP file
    xhr.open('POST', phpFileUrl, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // Get a message ready for displaying in the notices
    var message = null;
    var promptToDelete = false;

    // Get the date range
    var dateRange = dateRangePicker.value;

    // When the file is ready
    xhr.onreadystatechange = function() {
        // If the file has been successfully opened, watch it for progress
        if (xhr.readyState === 4) {
            // If we got a valid response, interpret it
            if (xhr.status === 200) {
                // Parse JSON response
                var response = {"status" : 1};
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (error) {
                    // This means we have the CSV and not some response
                    console.log("Generating CSV...");
                }

                // Handle response
                if (response.status == 1) {
                    // File download initiated successfully
                    var blob = new Blob([xhr.response], {type: 'text/csv'});

                    // Create a URL for the blob
                    var url = window.URL.createObjectURL(blob);

                    // Create a temporary anchor element
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = (dateRange ? dateRange + "_results.csv" : "full_backup.csv");

                    // Programmatically click the anchor to trigger download
                    document.body.appendChild(a);
                    a.click();

                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);

                    // Cleanup
                    setTimeout(() => {
                        // Remove the loading alert
                        updateAlert.remove();
                        deleteBtn.click();
                    }, 1000);

                    message = "Successfully backed up " + (dateRange ? dateRange : "all") + " results.";
                    promptToDelete = true;

                    // Provide confirmation
                    document.title = "Backup/Delete";
                } else {
                    // Cleanup
                    setTimeout(() => {
                        // Handle error
                        alert("Error: " + response.message);
                        // Remove the loading alert
                        updateAlert.remove();
                    }, 1000);
                    message = response.message;
                }
            } else {
                // Cleanup
                setTimeout(() => {
                    // Handle error
                    alert("Error: " + xhr.status);
                    // Remove the loading alert
                    updateAlert.remove();
                }, 1000);
                message = "Something went wrong. Please try again.";
                // Handle error
            }

            // Create a div element
            var popupMessage = document.createElement('div');
            popupMessage.classList.add('notice');

            // Set its inner HTML
            popupMessage.innerHTML = `
            <div class="notice-content-wrapper">
                <div class="notice-content">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                        <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                    </svg>
                </div>
            </div>
            <p>${message}</p>`;

            // Get the notices container and append the popupMessage
            document.getElementById('notices').appendChild(popupMessage);

            const notices = document.querySelectorAll('#notices .notice');
            notices.forEach((notice, index) => {
                let timeout;

                // Calculate delay for each notice
                const delay = index * 200; // Adjust the delay time as needed

                // Add animation with delay
                setTimeout(() => {
                    notice.classList.add('animate-in');
                }, delay);

                // Function to start the timeout
                const startTimeout = () => {
                    timeout = setTimeout(() => {
                        notice.classList.add('animate-out');

                        setTimeout(() => {
                            notice.remove();
                        }, 1000);
                    }, 8000); // 8 seconds
                };

                // Start the initial timeout
                startTimeout();

                // Pause the timeout on hover
                notice.addEventListener('mouseenter', () => {
                    clearTimeout(timeout);
                });

                // Resume the timeout when not hovered
                notice.addEventListener('mouseleave', () => {
                    startTimeout();
                });

                // Add click event listener to toggle animate-out
                notice.addEventListener('click', function() {
                    notice.classList.toggle('animate-out');
                    setTimeout(() => {
                        notice.remove();
                    }, 1000);
                });
            });

            // If any input wrappers are selected, select the respective input.
            var inputWrappers = document.querySelectorAll(".input-wrapper");

            inputWrappers.forEach(inputWrapper => {
                inputWrapper.addEventListener('click', () => {
                    // Get the input wrapper's parent
                    var parentInput = inputWrapper.parentElement.querySelector('input');

                    if(parentInput) {
                        inputWrapper.parentElement.querySelector('input').focus();
                        inputWrapper.parentElement.querySelector('input').click();
                    }
                });
            });
        }
    };

    // Send the AJAX request
    xhr.send('dateRange=' + encodeURIComponent(dateRange));
});

deleteBtn.addEventListener('click', () => {
    // Get the current value within the daterange picker.
    var datesToDelete = dateRangePicker.value;

    // Alter the delete form's dateRange
    document.querySelector('input.hidden-date-range').value = datesToDelete;

    document.getElementById('datesToDelete').innerHTML = datesToDelete ? datesToDelete : "all";
});