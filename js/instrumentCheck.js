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
    const modalWrapper = document.getElementById("instrumentModalWrapper");

    // TODO: How long until we want to assume the instruments are lost? (change to 120)
    const instrumentTimeout = 120000000000;

    tableBody.innerHTML = ""; // Clear existing rows
    var legitInstruments = 0;
    
    data.forEach((item) => {

        if(item.status) {
            legitInstruments++;
            // Create the table row
            const row = document.createElement("tr");
            row.setAttribute("id", `instrument${item.serial_number}`);
            row.setAttribute("class", "instrument");

            var lost = (Date.now() / 1000) - (item.last_connected ?? 0) > instrumentTimeout;
    
            if(lost) {
                row.classList.add("lost");
            }
    
            row.innerHTML = `
                <td>${item.serial_number}</td>
                <td>${item.module_id}</td>
                <td>${getProcessText(item.status, item.time_remaining, item.fault_condition)}</td>
                <td class="hidden lg:table-cell">${item.tablet_id}</td>
                <td class="end">
                    <div class="table-controls">
                        <div class="tooltip" title="${lost ? "Lost Connection" : getStatusText(item.status)}">
                            ${getStatusIcon(item.status)}
                        </div>
                        <button class="details tooltip" title="View Details">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                <path d="M288 80c-65.2 0-118.8 29.6-159.9 67.7C89.6 183.5 63 226 49.4 256c13.6 30 40.2 72.5 78.6 108.3C169.2 402.4 222.8 432 288 432s118.8-29.6 159.9-67.7C486.4 328.5 513 286 526.6 256c-13.6-30-40.2-72.5-78.6-108.3C406.8 109.6 353.2 80 288 80zM95.4 112.6C142.5 68.8 207.2 32 288 32s145.5 36.8 192.6 80.6c46.8 43.5 78.1 95.4 93 131.1c3.3 7.9 3.3 16.7 0 24.6c-14.9 35.7-46.2 87.7-93 131.1C433.5 443.2 368.8 480 288 480s-145.5-36.8-192.6-80.6C48.6 356 17.3 304 2.5 268.3c-3.3-7.9-3.3-16.7 0-24.6C17.3 208 48.6 156 95.4 112.6zM288 336c44.2 0 80-35.8 80-80s-35.8-80-80-80c-.7 0-1.3 0-2 0c1.3 5.1 2 10.5 2 16c0 35.3-28.7 64-64 64c-5.5 0-10.9-.7-16-2c0 .7 0 1.3 0 2c0 44.2 35.8 80 80 80zm0-208a128 128 0 1 1 0 256 128 128 0 1 1 0-256z"/>
                            </svg>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
    
            // Check if the modal exists
            var modal = document.getElementById(`instrument${item.serial_number}Modal`);
    
            if(!modal) {
                // Create the modal
                modal = document.createElement("div");
                modal.setAttribute("id", `instrument${item.serial_number}Modal`);
                modal.setAttribute("class", 'instrument-modal');
                modalWrapper.appendChild(modal);
            }
    
            // Update the modal's innerHTML
            modal.innerHTML = `
                <div class="close-instrument-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                    </svg>
                </div>
                <h2>Serial: ${item.serial_number}</h2>
                ${item.module_id !== null ? `<p>Module ID: ${item.module_id}</p>` : ''}
                ${item.status !== null ? `<p>Status: ${getProcessText(item.status, item.time_remaining, item.fault_condition)}</p>` : ''}
                ${item.progress !== null ? `<p>Progress: ${item.progress}</p>` : ''}
                ${item.time_remaining !== null ? `<p>Time Remaining: ${item.time_remaining}</p>` : ''}
                ${item.fault_code !== null ? `<p>Fault Code: ${item.fault_code}</p>` : ''}
                ${item.version_number !== null ? `<p>Version Number: ${item.version_number}</p>` : ''}
                ${item.last_connected !== null ? `<p>Last Connected: ${item.last_connected}</p>` : ''}
                ${item.last_qc_pass !== null ? `<p>Last QC Pass: ${item.last_qc_pass}</p>` : ''}
                ${item.qc_flag !== null ? `<p>QC Flag: ${item.qc_flag}</p>` : ''}
                ${item.tablet_it !== null ? `<p>Tablet: ${item.tablet_id}</p>` : ''}
            `;
        }
    });

    if(!data.length || !legitInstruments) {
        tableHead.classList.add('hidden');
        tableBody.innerHTML = `<td>There are no instruments in the database. Please add some by connecting with the tablet.</td>`;
    } else {
        tableHead.classList.remove('hidden');
    }
}

// Function to get status text based on status code and progress
function getProcessText(status, timeRemaining, error) {
    if (status == 1) {
        return "Idle";
    } else if (status == 2) {
        return `Running (${timeRemaining}s)`;
    } else if (status == 3) {
        return "Test Aborted";
    } else if (status == 4) {
        return "Result Available";
    } else if (status == 5) {
        return "Error: " + error
    } else {
        return "Unknown";
    }
}

// Function to get status icon based on status code
function getStatusIcon(status) {
    var iconText = '';
    switch(parseInt(status)) {
        case 0:
            iconText = '';
            break;
        case 1:
            iconText = 'pending';
            break;
        default:
            iconText = 'active';
    }
    return `<div class="status-indicator ${iconText}"></div>`;
}

// Function to get status icon based on status code
function getStatusText(status) {
    var iconText = '';
    switch(parseInt(status)) {
        case 0:
            iconText = 'Lost';
            break;
        case 1:
            iconText = 'Idle';
            break;
        default:
            iconText = 'Active';
    }
    return iconText;
}

document.addEventListener('click', (e) => {
    var target = e.target;
    var closestInstrument = null;
    var instrumentModals = document.querySelectorAll('.instrument-modal');

    instrumentModals.forEach((modal) => {
        if(!modal.contains(e.target) || target.classList.contains('close-instrument-modal')) {
            modal.classList.remove('active');
        }
    });

    while(target && !closestInstrument) {
        if(target.classList.contains('instrument')) {
            closestInstrument = target;
        } else {
            target = target.parentElement;
        }
    }

    if(closestInstrument) {
        document.getElementById(`${closestInstrument.id}Modal`).classList.add('active');
    }
})