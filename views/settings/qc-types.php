<?php // QC Type Settings

// Get all instrument QC types
$qcTestTypes = getInstrumentQCTypes(true);
?>

<section class="notice mt-2 !mb-0">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>These are the QC test types which invoke alerts on the Assay Modules. The time interval dictates the length of time it takes for a test to expire. The result count dictates how many results should be processed before the test expires. Leave either or both of these blank to remove all checks and have this test type be fully manual.</p>
</section>

<!-- Define QC Tests for Assay Modules -->
<form id="qcTestTypeForm" method="POST" class="mt-2 items-start w-full max-w-4xl" action="/process">
    <input type="hidden" name="action" value="qc-types">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">


    <?php if(!$qcTestTypes) : ?>
        <p id="noTestTypes" class="text-center w-full">You currently have no QC Test Types. Please add one below to enable QC for instruments.</p>
    <?php else : ?>
        <table id="qcTable">
            <thead>
                <th>#</th>
                <th>QC Name</th>
                <th>Time Interval (Days)</th>
                <th>Result Count</th>
                <th></th>
            </thead>

            <tbody id="qcTableBody">
                <?php foreach($qcTestTypes as $testType) : ?>
                    <tr class="qc-table" data-row-id="<?= $testType['id'];?>">
                        <td><?= $testType['id'];?></td>
                        <td><input required type="text" value="<?= $testType['name'];?>"></td>
                        <td><input type="number" value="<?= $testType['time_intervals'];?>"></td>
                        <td><input type="number" value="<?= $testType['result_intervals'];?>"></td>
                        <td>
                            <button class="remove-row-btn">
                                <svg viewBox="0 0 448 512">
                                    <path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    <?php endif;?>

    <!-- Add a new row -->
     <div class="flex flex-col items-center w-full gap-4">
         <button id="addTestBtn" class="flex justify-center gap-2 items-center rounded-xl px-4 py-1.5 text-dark transition-all duration-500 hover:scale-105">
             <span class="text-nowrap">Add new QC test type</span>
             <svg class="h-full max-h-[30px] fill-dark bg-white rounded-full aspect-square p-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                 <path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z"/>
             </svg>
         </button>

         <button id="qcTypeSubmit" class="btn smaller-btn" type="submit">Save Settings</button>
     </div>
</form>

<!-- Modal for deleting rows -->
<div id="genericModalWrapper" class="modal-wrapper">
    <div id="deleteModal" class="generic-modal">
        <div class="close-modal" data-modal-close>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
            </svg>
        </div>
        <svg class="fill-red-500 h-10 w-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
        </svg>
        <p class="text-center">Are you sure you want to delete the QC Test Type: <span id="datesToDelete" class="font-black text-red-500"></span> results?</p>
        <form action="/process" method="POST">
            <input type="hidden" name="action" value="delete-results">
            <input type="hidden" name="return-path" value="<?= $currentURL;?>">
            <input type="hidden" name="dateRange" class="hidden-date-range" value="">
            <div class="w-full flex justify-center items-center gap-3 mt-3">
                <button id="confirmRemove" class="btn smaller-btn">Yes</button>
                <button id="cancelRemove" class="cursor-pointer btn smaller-btn close-modal no-styles" data-modal-close>Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const addTestTypeButton = document.getElementById("addTestBtn");
        const genericModalWrapper = document.getElementById("genericModalWrapper");
        const confirmRemoveButton = document.getElementById("confirmRemove");
        const cancelRemoveButton = document.getElementById("cancelRemove");
        const datesToDeleteSpan = document.getElementById("datesToDelete");

        // Initialise variables for managing state of row selectors
        let rowIdToDelete = null;
        let qcTestTypes = <?= json_encode($qcTestTypes); ?>;

        // Get lowest number
        function getLowestMissingNumber(numbers) {
            var i = 1;

            numbers.forEach((num) => {
                if(i !== num) {
                    return i;
                }
                i++;
            });

            return i;
        }

        // Function to add a new row
        function addNewRow() {
            // Check if we have a table
            var table = document.getElementById("qcTable");

            if(!table) {
                // Make the table!
                const qcTableHTML = `
                    <table id="qcTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>QC Name</th>
                                <th>Time Interval (Days)</th>
                                <th>Result Count</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="qcTableBody"></tbody>
                    </table>
                `;

                // Replace the element with ID 'noTestTypes' with the new table
                var noTestTypesElement = document.getElementById('noTestTypes');
                if (noTestTypesElement) {
                    noTestTypesElement.outerHTML = qcTableHTML;
                }
            }

            // Get the table body
            var qcTableBody = document.getElementById("qcTableBody");

            // Get all the IDs of all the existing rows
            var tableRows =  qcTableBody.querySelectorAll('.qc-table');

            var tableIds = [];

            // Get the first ID that hasn't been used
            tableRows.forEach((tableRow) => {
                var id = Number(tableRow.dataset.rowId);
                tableIds.push(id);
            });

            // Sort and return the lowest available ID
            tableIds.sort();
            var newRowId = getLowestMissingNumber(tableIds);

            const row = document.createElement("tr");
            row.classList.add("qc-table");
            row.dataset.rowId = newRowId;

            row.innerHTML = `
                <td>${newRowId}</td>
                <td><input required type="text" value="" placeholder="QC Name"></td>
                <td><input type="number" value="" placeholder="Time Interval (Days)"></td>
                <td><input type="number" value="" placeholder="Result Count"></td>
                <td>
                    <button type="button" class="remove-row-btn">
                        <svg viewBox="0 0 448 512">
                            <path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                        </svg>
                    </button>
                </td>
            `;

            qcTableBody.appendChild(row);
            qcTestTypes.push({ id: newRowId, name: "", time_intervals: "", result_intervals: "" });

            // Attach event listener to the new remove button
            row.querySelector(".remove-row-btn").addEventListener("click", (e) => {
                e.preventDefault();
                showRemoveConfirmation(newRowId);
            });
        }

        // Function to prepare data for submission
        function prepareDataForSubmission() {
            let allRequiredFieldsFilled = true; // Flag to check if all required fields are provided

            // Get the table body
            var qcTableBody = document.getElementById("qcTableBody");
            console.log(qcTestTypes);

            qcTestTypes.forEach((qc) => {
                const row = qcTableBody.querySelector(`tr[data-row-id="${qc.id}"]`);
                if (row) {
                    const requiredInputs = row.querySelectorAll("input[required]");

                    // Check if all required fields in the row are filled
                    requiredInputs.forEach(input => {
                        if (!input.value.trim()) {
                            allRequiredFieldsFilled = false;
                        }
                    });

                    const inputs = row.querySelectorAll("input");
                    inputs.forEach(input => {
                        input.classList.remove("error"); // Remove error highlight if data is valid
                        // Update qcTestTypes based on the input type
                        if (input.type === "text") {
                            qc.name = input.value;
                        }
                        else if (input === row.querySelectorAll("input[type='number']")[0]) {
                            console.log("NAME CHANGED");
                            qc.time_intervals = input.value;
                        }
                        else if (input === row.querySelectorAll("input[type='number']")[1])
                        {
                            qc.result_intervals = input.value;
                        }
                    })
                }
            });

            // Prevent submission if any required fields are missing
            if (!allRequiredFieldsFilled) {
                alert("Please fill in all required fields.");
                return; // Stop function execution to prevent form submission
            }

            // Prepare hidden input for form submission if all required fields are filled
            const hiddenInput = document.createElement("input");
            hiddenInput.type = "hidden";
            hiddenInput.name = "qcTestTypes";
            hiddenInput.value = JSON.stringify(qcTestTypes);
            document.querySelector("#qcTestTypeForm").appendChild(hiddenInput);
            document.querySelector("#qcTestTypeForm").submit();
        }

        // Show confirmation modal for removing a row
        function showRemoveConfirmation(rowId) {
            rowIdToDelete = rowId;

            // Get the table body
            var qcTableBody = document.getElementById("qcTableBody");

            const row = qcTableBody.querySelector(`tr[data-row-id="${rowId}"]`);
            const testName = row.querySelector("input[type='text']").value || null;

            if(!testName) {
                // Just remove as it's likely that the test has not been created anyway
                removeRow(rowId);
                return;
            }

            // Update modal content
            datesToDeleteSpan.textContent = `"${testName}"`;

            // Display modal with "active" class
            genericModalWrapper.querySelector('.generic-modal').classList.add("active");
            genericModalWrapper.classList.add("active");
        }

        // Remove row from UI and array
        function removeRow(rowId) {
            // Get the table body
            var qcTableBody = document.getElementById("qcTableBody");

            const row = qcTableBody.querySelector(`tr[data-row-id="${rowId}"]`);
            if (row) {
                row.remove();
                qcTestTypes = qcTestTypes.filter(item => item.id !== rowId);
            }
        }

        // Get the table body
        var qcTableBody = document.getElementById("qcTableBody");

        // Attach event listeners to existing remove buttons
        if(qcTableBody) {
            qcTableBody.querySelectorAll(".remove-row-btn").forEach((button, index) => {
                button.addEventListener("click", (e) => {
                    e.preventDefault();
                    showRemoveConfirmation(qcTestTypes[index].id);
                });
            });
        }

        // Event listeners for modal confirm and cancel
        confirmRemoveButton.addEventListener("click", (e) => {
            e.preventDefault();
            if (rowIdToDelete !== null) {
                removeRow(rowIdToDelete);
                rowIdToDelete = null;
            }
            genericModalWrapper.querySelector('.generic-modal').classList.remove("active");
            genericModalWrapper.classList.remove("active");
        });

        cancelRemoveButton.addEventListener("click", (e) => {
            e.preventDefault();
            genericModalWrapper.querySelector('.generic-modal').classList.remove("active");
            genericModalWrapper.classList.remove("active");
            rowIdToDelete = null;
        });

        // Event listeners for add and submit buttons
        addTestTypeButton.addEventListener("click", (e) => {
            e.preventDefault();
            addNewRow();
        });

        document.querySelector("#qcTypeSubmit").addEventListener("click", (e) => {
            e.preventDefault();
            prepareDataForSubmission();
        });
    });


</script>