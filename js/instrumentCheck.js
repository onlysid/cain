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
                var data = JSON.parse(xhr.responseText);
                
                updateTable(data);
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
var id = setInterval(getInstrumentDetails, 5000);

// Function to update table rows with JSON data
function updateTable(data) {
    const table = document.getElementById("instrumentsTable");
    const tableBody = document.getElementById("instrumentsTableBody");
    const tableHead = table.querySelector("thead");

    tableBody.innerHTML = ""; // Clear existing rows
    
    data.forEach((item, index) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.serial_number}</td>
            <td>${getProcessText(item.status, item.progress, item.fault_condition)}</td>
            <td class="end">
                <form method="POST" action="/process">
                    <div class="instrument-table-controls">
                        <input type="hidden" name="action" value="delete-instrument">
                        <input type="hidden" name="return-path" value="${window.location.href}">
                        <input type="hidden" name="instrument-id" value="${item.id}">
                        <div class="tooltip" title="${getStatusText(item.status)}">
                            ${getStatusIcon(item.status)}
                        </div>
                        <button class="table-button tooltip" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </td>
        `;
        tableBody.appendChild(row);
    });

    if(!data.length) {
        tableHead.classList.add('hidden');
        tableBody.innerHTML = `<td>There are no instruments in the database. Please add some by connecting with the tablet.</td>`;
    } else {
        tableHead.classList.remove('hidden');
    }
}

// Function to get status text based on status code and progress
function getProcessText(status, progress, error) {
    if (status === 1) {
        return "Idle";
    } else if (status === 2) {
        return `Running (${progress}%)`;
    } else if (status === 3) {
        return "Test Aborted";
    } else if (status === 4) {
        return "Result Available";
    } else if (status === 5) {
        return "Error: " + error
    } else {
        return "Unknown";
    }
}

// Function to get status icon based on status code
function getStatusIcon(status) {
    return `<div class="status-indicator ${(status == 1 ? "active" : "")}"></div>`;
}

// Function to get status icon based on status code
function getStatusText(status) {
    return (status === 1 ? "Active" : "Inactive");
}