/* 
    We make an AJAX request to lims-check.php.
    This returns a JSON object of each setting.
*/

function limsStatusCheck() {
    var statusSpan = document.getElementById("limsStatus");
    
    // Define the PHP file URL
    var phpFileUrl = '/scripts/lims-check.php';
    
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
                var data = JSON.parse(xhr.responseText);

                // If the data shows refresh = true, refresh the page
                if(data.refresh) {
                    alert("You've been logged out!");
                } else {
                    updateLimsStatus(data);
                }
                
            } else {
                // Handle error
                statusSpan.classList.remove("active");
                statusSpan.classList.remove("incative");
                statusSpan.title = "Checking...";
            }
        }
    };

    // Send the AJAX request
    xhr.send();
}

limsStatusCheck();

// Get notifications via AJAX every 10 seconds
var id = setInterval(limsStatusCheck, 10000);

// Function to update table rows with JSON data
function updateLimsStatus(data) {
    var commsStatus = data.value;
    var statusSpan = document.getElementById("limsStatus");
    var commsTooltip = statusSpan.querySelector('.tooltip');

    if(commsStatus == 1) {
        statusSpan.classList.add("active");
        statusSpan.classList.remove("inactive");
        commsTooltip.title = "Connected";
    } else {
        statusSpan.classList.remove("active");
        statusSpan.classList.add("inactive");
        commsTooltip.title = "Not Connected";
    }
}
