<?php // General Settings

// Hospital Info Settings Subset
$hospitalInfoKeys = ['hospital_name', 'office_name', 'hospital_location', 'date_format'];
$hospitalInfo = array_intersect_key($settings, array_flip($hospitalInfoKeys));

// Accepted Date Formats
$dateFormats = ["d M Y", "d F Y", "d/m/Y"];

?>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>This page is automatically updated every 10s with the latest information available about all Assay Modules in the network.</p>
</section>

<table id="instrumentsTable">
    <thead>
        <th></th>
        <th>Module ID</th>
        <th>Processes</th>
        <th>Status</th>
    </thead>

    <tbody id="instrumentsTableBody">
        <tr>
            <td>01</td>
            <td>SIIIAM0010</td>
            <td>Running (75%)</td>
            <td>
                <div class="flex gap-2.5 items-center">
                    <div class="h-4 w-4 aspact-square rounded-full bg-green-500"></div>
                    <svg class="h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                    </svg>
                </div>
            </td>
        </tr>
    </tbody>
</table>

<script type="module" src="/js/instrumentCheck.js"></script>