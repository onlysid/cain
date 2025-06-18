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

                // Get sort data from URL Params
                const urlParams = new URLSearchParams(window.location.search);
                const sortParam = urlParams.get('sp');
                const sortDirection = urlParams.get('sd');
                const searchParam = urlParams.get('s');
                const expired = urlParams.get('expired');

                // Sort the instruments
                sortInstruments(data, sortParam, sortDirection);

                // Filter instruments based on fuzzy search
                const filteredData = searchInstruments(data, searchParam);

                updateTable(filteredData, expired);
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

// Get notifications via AJAX every 5 seconds
var id = setInterval(getInstrumentDetails, 5000);

// Helper function to access nested properties
function getNestedValue(obj, path) {
    return path.split('.').reduce((acc, part) => acc && acc[part], obj);
}

// Helper function for fuzzy matching on relevant fields
function searchInstruments(instruments, searchQuery) {
    if (!searchQuery) return instruments; // Skip if no search query provided
    const query = searchQuery.toLowerCase();

    return instruments.filter(instrument =>
        (instrument.serial_number && instrument.serial_number.toLowerCase().includes(query)) ||
        (instrument.front_panel_id && instrument.front_panel_id.toLowerCase().includes(query)) ||
        (instrument.status && instrument.status.toString().includes(query)) ||
        (instrument.current_assay && instrument.current_assay.toLowerCase().includes(query))
    );
}

// Sort function
function sortInstruments(instruments, sortParam, sortDirection) {
    if (sortParam) {
        instruments.sort((a, b) => {
            const valA = getNestedValue(a, sortParam);
            const valB = getNestedValue(b, sortParam);

            if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
            if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    }
}

// Function to update table rows with JSON data
function updateTable(data, expired) {
    const table = document.getElementById("instrumentsTable");
    const tableBody = document.getElementById("instrumentsTableBody");
    const tableHead = table.querySelector("thead");
    const modalWrapper = document.getElementById("instrumentModalWrapper");

    // How long until we want to assume the instruments are lost? (5 minutes)
    const instrumentTimeout = 600;

    const showHiddenInstruments = expired;
    console.log(showHiddenInstruments);

    tableBody.innerHTML = ""; // Clear existing rows
    var legitInstruments = 0;
    var unexpiredInstruments = 0;

    data.forEach((item) => {
        var date = new Date();

        // Get current unix timestamp
        var now = Math.floor(date / 1000) - (date.getTimezoneOffset() * 60);

        // Get time remaining
        var timeRemaining = Math.floor(((item.assay_start_time + item.duration) - now) / 60);

        // If it goes below 0, say pending.
        if(timeRemaining < 0) {
            timeRemaining = "<1 min";
        } else {
            timeRemaining = timeRemaining + " mins";
        }

        if(item.status != null) {
            legitInstruments++;

            // Create the table row
            const row = document.createElement("tr");
            row.setAttribute("id", `instrument${item.serial_number}`);
            row.setAttribute("class", "instrument");
            row.setAttribute("data-modal-open", "instrument" + item.id)

            // Determine if the instrument is lost
            var lost = now - (item.last_connected ?? 0) > instrumentTimeout;
            if(lost) {
                row.classList.add("lost");
            } else {
                unexpiredInstruments++;
            }

            // Add a class if the instrument is locked
            if(item.locked) {
                row.classList.add("locked");
            }

            // Get the QC Icon
            var qcIcon = `
                <div class="wrapper tooltip qc w-[2.5rem] !flex gap-1 items-center" title="QC Untested">
                    QC
                    <svg class="!fill-dark" viewBox="0 0 512 512">
                        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM169.8 165.3c7.9-22.3 29.1-37.3 52.8-37.3l58.3 0c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24l0-13.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1l-58.3 0c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/>
                    </svg>
                </div>
            `;

            switch(item.qc.pass) {
                case 1:
                    qcIcon = `
                        <div class="wrapper tooltip qc w-[2.5rem] !flex gap-1 items-center" title="QC Passed">
                            QC
                            <svg class="!fill-green-500" viewBox="0 0 512 512">
                                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                            </svg>
                        </div>
                    `;
                    break;
                case 2:
                    qcIcon = `
                        <div class="wrapper tooltip qc w-[2.5rem] !flex gap-1 items-center" title="QC Expired">
                            QC
                            <svg class="!fill-amber-500" viewBox="0 0 384 512">
                                <path d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64l0 11c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437l0 11c-17.7 0-32 14.3-32 32s14.3 32 32 32l32 0 256 0 32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-11c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1l0-11c17.7 0 32-14.3 32-32s-14.3-32-32-32L320 0 64 0 32 0zM96 75l0-11 192 0 0 11c0 25.5-10.1 49.9-28.1 67.9L192 210.7l-67.9-67.9C106.1 124.9 96 100.4 96 75z"/>
                            </svg>
                        </div>
                    `;
                    break;
                case 0:
                    qcIcon = `
                        <div class="wrapper tooltip qc w-[2.5rem] !flex gap-1 items-center" title="QC Failed">
                            QC
                            <svg class="!fill-red-500" viewBox="0 0 512 512">
                                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                            </svg>
                        </div>
                    `;
                default:
                    break;
            }

            row.innerHTML = `
                <td>${item.module_name ?? item.serial_number}</td>
                <td>${item.front_panel_id}</td>
                <td>${lost ? "" :getProcessText(item.status, timeRemaining, item.fault_condition)}</td>
                <td class="end">
                    <div class="table-controls">
                        ${qcIcon}
                        ${item.locked ? `
                            <div class="wrapper tooltip" title="Locked">
                                <svg title="Locked" class="pb-0.5 fill-red-500" viewBox="0 0 448 512">
                                    <path d="M144 144l0 48 160 0 0-48c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192l0-48C80 64.5 144.5 0 224 0s144 64.5 144 144l0 48 16 0c35.3 0 64 28.7 64 64l0 192c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 256c0-35.3 28.7-64 64-64l16 0z"/>
                                </svg>
                            </div>
                        ` : `
                            <div class="wrapper tooltip" title="Unlocked">
                                <svg title="Locked" class="pb-0.5 fill-green-500" viewBox="0 0 448 512">
                                    <path d="M144 144c0-44.2 35.8-80 80-80c31.9 0 59.4 18.6 72.3 45.7c7.6 16 26.7 22.8 42.6 15.2s22.8-26.7 15.2-42.6C331 33.7 281.5 0 224 0C144.5 0 80 64.5 80 144l0 48-16 0c-35.3 0-64 28.7-64 64L0 448c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-192c0-35.3-28.7-64-64-64l-240 0 0-48z"/>
                                </svg>
                            </div>
                        `}
                        <div class="tooltip" title="${lost ? "Lost Connection" : getStatusText(item.status)}">
                            ${getStatusIcon(item.status)}
                        </div>
                        <a href="/assay-modules/${item.id}" class="details tooltip" title="View Details">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                <path d="M288 80c-65.2 0-118.8 29.6-159.9 67.7C89.6 183.5 63 226 49.4 256c13.6 30 40.2 72.5 78.6 108.3C169.2 402.4 222.8 432 288 432s118.8-29.6 159.9-67.7C486.4 328.5 513 286 526.6 256c-13.6-30-40.2-72.5-78.6-108.3C406.8 109.6 353.2 80 288 80zM95.4 112.6C142.5 68.8 207.2 32 288 32s145.5 36.8 192.6 80.6c46.8 43.5 78.1 95.4 93 131.1c3.3 7.9 3.3 16.7 0 24.6c-14.9 35.7-46.2 87.7-93 131.1C433.5 443.2 368.8 480 288 480s-145.5-36.8-192.6-80.6C48.6 356 17.3 304 2.5 268.3c-3.3-7.9-3.3-16.7 0-24.6C17.3 208 48.6 156 95.4 112.6zM288 336c44.2 0 80-35.8 80-80s-35.8-80-80-80c-.7 0-1.3 0-2 0c1.3 5.1 2 10.5 2 16c0 35.3-28.7 64-64 64c-5.5 0-10.9-.7-16-2c0 .7 0 1.3 0 2c0 44.2 35.8 80 80 80zm0-208a128 128 0 1 1 0 256 128 128 0 1 1 0-256z"/>
                            </svg>
                        </a>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);

            // Check if the modal exists
            var modal = document.getElementById(`instrument${item.serial_number}Modal`);

            if(!modal) {
                // Create the modal
                modal = document.createElement("div");
                modal.setAttribute("id", `instrument${item.id}`);
                modal.setAttribute("class", 'generic-modal instrument-modal');
                modalWrapper.appendChild(modal);
            }

            // Update the modal's innerHTML
            modal.innerHTML = `
                <div class="instrument-icons-wrapper">
                    <div class="close-modal" data-modal-close>
                        <svg viewBox="0 0 512 512">
                            <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                        </svg>
                    </div>
                </div>
                <div class="instrument-modal-header">
                    <h2 class="truncate-text">${item.module_name ?? item.serial_number}</h2>
                    |
                    ${item.locked ? `
                        <div class="tooltip" title="Locked">
                            <svg class="locked" viewBox="0 0 448 512">
                                <path d="M144 144l0 48 160 0 0-48c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192l0-48C80 64.5 144.5 0 224 0s144 64.5 144 144l0 48 16 0c35.3 0 64 28.7 64 64l0 192c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 256c0-35.3 28.7-64 64-64l16 0z"/>
                            </svg>
                        </div>
                    ` : `
                        <div class="tooltip" title="Unlocked">
                            <svg class="icon" viewBox="0 0 448 512">
                                <path d="M144 144c0-44.2 35.8-80 80-80c31.9 0 59.4 18.6 72.3 45.7c7.6 16 26.7 22.8 42.6 15.2s22.8-26.7 15.2-42.6C331 33.7 281.5 0 224 0C144.5 0 80 64.5 80 144l0 48-16 0c-35.3 0-64 28.7-64 64L0 448c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-192c0-35.3-28.7-64-64-64l-240 0 0-48z"/>
                            </svg>
                        </div>
                    `}
                    |
                    ${qcIcon}
                </div>
                <div class="divider"></div>
                ${item.front_panel_id !== null ? `<p><span class="font-black">Module ID:</span> ${item.front_panel_id}</p>` : ''}
                ${item.module_name !== null ? `<p><span class="font-black">Module Name:</span> ${item.module_name ?? '-'}</p>` : ''}
                ${item.serial_number !== null ? `<p><span class="font-black">Serial Number:</span> ${item.serial_number}</p>` : ''}
                ${item.status !== null ? `<p><span class="font-black">Status:</span> ${getProcessText(item.status, timeRemaining, item.fault_condition)}</p>` : ''}
                ${item.current_assay !== null ? `<p><span class="font-black">Current Assay:</span> ${item.current_assay ?? 'No assay running'}</p>` : ''}
                ${item.device_error !== null ? `<p><span class="font-black">Error:</span> ${item.device_error}</p>` : ''}
                ${item.last_connected !== null && item.last_connected !== 0 ? `<p><span class="font-black">Last Connected:</span> ${(new Date(item.last_connected * 1000).toLocaleString('en-GB'))}</p>` : ''}
                <div class="divider"></div>
                <a href="/assay-modules/${item.id}" class="btn smaller-btn">View More Details</a>
            `;
        }
    });

    if(!data.length || !legitInstruments || (!unexpiredInstruments && !showHiddenInstruments)) {
        tableHead.classList.add('hidden');
        tableBody.innerHTML = `<td>There are no` + (showHiddenInstruments || unexpiredInstruments ? `` : ` active`) + ` instruments in the database. Please add some by connecting with the tablet or refine your search.</td>`;
    } else {
        tableHead.classList.remove('hidden');
    }
}

// Function to get status text based on status code and progress
function getProcessText(status, timeRemaining, error) {
    if (status == 1) {
        return "Idle";
    } else if (status == 2) {
        return "Preparing Assay";
    } else if (status == 3) {
        return `Running (${timeRemaining} remaining)`;
    } else if (status == 4) {
        return "Aborting";
    } else if (status == 5) {
        return "Result Available";
    } else if (status == 6) {
        return "Error: " + error;
    } else if (status == 7) {
        return "Uninitialising";
    } else if (status == 8) {
        return "Initialising";
    } else if (status == 9) {
        return "Assay Complete";
    } else if (status == 99) {
        return "Disconnected";
    } else {
        return "Unknown";
    }
}

// Function to get status icon based on status code
function getStatusIcon(status) {
    var iconText = '';
    switch(parseInt(status)) {
        case 0:
        case 99:
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
        case 99:
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