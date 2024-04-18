<?php // General Settings
$hospitalInfo = systemInfo();

// Extract 'name' as keys and 'value' as values
$settings = array_column($hospitalInfo, 'value', 'name');

// Hospital Info Settings Subset
$hospitalInfoKeys = ['hospital_name', 'office_name', 'hospital_location', 'date_format'];
$hospitalInfo = array_intersect_key($settings, array_flip($hospitalInfoKeys));

// Accepted Date Formats
$dateFormats = ["d M Y", "d F Y", "d/m/Y"];
?>

<h1>Assay Modules</h1>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>This page is automatically updated every 5s with the latest information available about all Assay Modules in the network.</p>
</section>

<table id="instrumentsTable">
    <thead>
        <th>Serial No</th>
        <th>Module ID</th>
        <th>Status</th>
        <th class="hidden lg:table-cell">Tablet</th>
        <th></th>
    </thead>

    <tbody id="instrumentsTableBody">

    </tbody>
</table>

<div id="instrumentModalWrapper">
    <div class="overlay"></div>
</div>

<script type="module" src="/js/instrumentCheck.js"></script>