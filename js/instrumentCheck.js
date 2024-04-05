/* 
    We make an AJAX request to instrument-check.php.
    This returns a JSON object of each instrument with their latest database information.
*/

function getInstrumentDetails() {
    var statusSpan = document.getElementById("statusSpan");
    
    // Define the PHP file URL
    var phpFileUrl = '/scripts/instrument-check.php';
    
    // Create a new XMLHttpRequest object
    var xhr = new XMLHttpRequest();

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

                statusSpan.innerHTML = responseText;
            } else {
                // Handle error
                statusSpan.innerHTML = 'Error updating. Please refresh and try again.';
            }
        }
    };

    // Send the AJAX request
    xhr.send();
}

getInstrumentDetails();

// Get notifications via AJAX every 30 seconds
var id = setInterval(getInstrumentDetails, 10000);