<?php // Updating cover
include BASE_DIR . '/includes/version.php';

// Check for TOM updates
$areWeUpdating = false;
$areWeUpdating = checkForUpdates($version);
if($areWeUpdating) : ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Display a loading message
        // Create the update alert element
        var updateAlert = document.createElement('div');
        updateAlert.id = 'updateAlert';
        // Get the main element
        var mainElement = document.querySelector('main');
        // Insert the update alert as the first child of the main element
        mainElement.insertAdjacentElement('afterbegin', updateAlert);
        <?php if($areWeUpdating === 100) : ?>
            updateAlert.innerHTML = '<h2>TOM is updating!</h2><p>Please come back later. Alternatively, click anywhere to reload the page and clear your cache.</p><div class="response-code">Contact support if this message persists.</div>';
            document.addEventListener('click', () => {
                location.reload(true);
            });
        <?php else : ?>
            updateAlert.innerHTML = '<h2>TOM is updating...</h2><p>Please do not leave this page, this process may take a while.</p>';
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

            // Create a new XMLHttpRequest object
            var xhr = new XMLHttpRequest();

            // Define the PHP file URL
            var phpFileUrl = '/admin/db-update.php';

            // Open a GET request to the PHP file
            xhr.open('GET', phpFileUrl, true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    var updateDiv = document.getElementById('updateAlert');
                    if (xhr.status === 200) {
                        var responseText = xhr.responseText;
                        var validResponse = responseText == "Successfully updated.";
                        updateDiv.style.backgroundColor = validResponse ? "green" : "red";
                        updateDiv.innerHTML = (validResponse ? ("<h2>Update complete!</h2>") : ("<h2>Something went wrong!</h2>")) + "<p>Click anywhere to dismiss this message.</p><div class='response-code'>Response: " + responseText + "</div>";
                        document.addEventListener('click', () => {
                            location.reload(true);
                        });
                        
                    } else {
                        // Handle error
                        updateDiv.innerHTML = 'Error updating. Please refresh and try again.';
                    }
                }
            };

            // Send the AJAX request
            xhr.send();
        <?php endif;?>
    });
</script>
<?php endif;?>