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

    </tbody>
</table>

<script type="module" src="/js/instrumentCheck.js"></script>