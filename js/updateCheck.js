// Wait for the page to load and display a loading message via AJAX
document.addEventListener('DOMContentLoaded', function() {
    // Create the update alert element
    var updateAlert = document.createElement('div');
    updateAlert.id = 'updateAlert';

    // Get the main element
    var mainElement = document.querySelector('main');

    // Insert the update alert as the first child of the main element
    mainElement.insertAdjacentElement('afterbegin', updateAlert);

    // Set the document title
    document.title = "Updating...";

    // Set the container html
    updateAlert.innerHTML = '<h2>Samba HUB is updating...</h2><p>This process may take a while.</p>';

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

    // Append the container to the updateAlert element
    updateAlert.appendChild(container);

    // Create a new XMLHttpRequest object
    var xhr = new XMLHttpRequest();

    // Disable built-in timeout
    xhr.timeout = 0;

    // Custom refresh logic
    var refreshTimeout = 30000;
    var maxRefreshAttempts = 10;
    // Use localStorage to store the count of refresh attempts.
    var refreshAttempts = parseInt(localStorage.getItem('updateRefreshAttempts') || "0", 10);

    // Set a timer that will refresh the page if no response is received in 30s.
    var refreshTimer = setTimeout(function() {
        refreshAttempts++;
        localStorage.setItem('updateRefreshAttempts', refreshAttempts);
        if (refreshAttempts < maxRefreshAttempts) {
            // Refresh the page
            location.reload(true);
        } else {
            // Stop auto-refreshing after max attempts and display an error
            updateAlert.innerHTML = '<h2>Something went wrong!</h2><p>Error updating after multiple attempts. Please refresh manually.</p>';
            document.title = "Something went wrong...";
        }
    }, refreshTimeout);

    // Define the PHP file URL
    var phpFileUrl = '/admin/db-update.php';

    // Open a GET request to the PHP file
    xhr.open('GET', phpFileUrl, true);

    // When the file is ready
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            // Clear the refresh timer and reset the counter
            clearTimeout(refreshTimer);
            localStorage.removeItem('updateRefreshAttempts');

            // If we got a valid response, interpret it
            if (xhr.status === 200) {
                var responseText = xhr.responseText;
                // What we expect is either "Successfully updated." or an empty response.
                var validResponse = responseText === "Successfully updated." || responseText === "";
                if (responseText === "") {
                    // We updated successfully!
                    responseText = "N/A";
                }

                // Display different messages based on whether the update succeeded.
                updateAlert.style.backgroundColor = validResponse ? "#217153" : "red";
                updateAlert.innerHTML = (validResponse ? ("<h2>Update complete!</h2>") : ("<h2>Something went wrong!</h2>")) +
                    "<p>Click anywhere to dismiss this message.</p><div class='response-code show-scrollbar'>Response: " + responseText + "</div>";
                document.title = validResponse ? "Update complete!" : "Something went wrong...";

                // Add an option to hard refresh page on click, unless text is selected.
                document.addEventListener('click', () => {
                    if (window.getSelection().toString().length > 0) {
                        return;
                    }
                    location.reload(true);
                });
            } else {
                // Handle error status codes
                updateAlert.innerHTML = '<h2>Error updating.</h2><p>Please refresh and try again.</p>';
            }
        }
    };

    // Send the AJAX request
    xhr.send();
});
