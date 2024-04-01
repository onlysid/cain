<?php // Updating cover
include BASE_DIR . '/includes/version.php';

// Check for Cain updates
$areWeUpdating = false;
$areWeUpdating = checkForUpdates($version);

// If we are updating, load a cover screen with JS.
if($areWeUpdating === true || $areWeUpdating === 100) : ?>
    <script>
        // Wait for the page to load and display a loading message
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
            updateAlert.innerHTML = '<h2>Cain is updating...</h2><p>This process may take a while.</p>';

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

            // When the file is ready
            xhr.onreadystatechange = function() {
                // If the file has been successfully opened, watch it for progress
                if (xhr.readyState === 4) {

                    // If we got a valid response, interpret it
                    if (xhr.status === 200) {
                        // The response code/message
                        var responseText = xhr.responseText;

                        // What we expect
                        var validResponse = responseText == "Successfully updated.";

                        // Display different messages based on if the updated succeeded or not.
                        updateAlert.style.backgroundColor = validResponse ? "green" : "red";
                        updateAlert.innerHTML = (validResponse ? ("<h2>Update complete!</h2>") : ("<h2>Something went wrong!</h2>")) + "<p>Click anywhere to dismiss this message.</p><div class='response-code'>Response: " + responseText + "</div>";
                        document.title = validResponse ? "Update complete!" : "Something went wrong...";

                        // Add an option to hard refresh page on page click
                        document.addEventListener('click', () => {
                            location.reload(true);
                        });
                    } else {
                        // Handle error
                        updateAlert.innerHTML = 'Error updating. Please refresh and try again.';
                    }
                }
            };

            // Send the AJAX request
            xhr.send();
        });
    </script>
<?php elseif($areWeUpdating === 200) : ?>
    <form action="process" method="POST" class="fixed bottom-0 z-50 bg-red-500 w-full flex justify-center py-5 px-4">
        <input type="hidden" name="action" value="reset-db-version">
        <input type="hidden" name="return-path" value="<?= $currentURL;?>">
        <p class="text-white text-center">Warning: Database may be corrupted. Please speak with an admin or <button type="submit" class="!text-blue-100 underline hover:!text-green-100">try safely resetting (click here).</button></p>
    </form>
<?php endif;?>