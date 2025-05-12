<?php // General Settings
$hospitalInfo = systemInfo();

// Extract 'name' as keys and 'value' as values
$settings = array_column($hospitalInfo, 'value', 'name');

// Hospital Info Settings Subset
$hospitalInfoKeys = ['hospital_name', 'office_name', 'hospital_location', 'date_format'];
$hospitalInfo = array_intersect_key($settings, array_flip($hospitalInfoKeys));

// Accepted Date Formats
$dateFormats = ["d M Y", "d F Y", "d/m/Y"];

// Do we have any filters?
$filters = $_GET ?? $session->get('result-filters');

?>
<h1>Assay Modules</h1>

<!-- <section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>This page is automatically updated every 5s with the latest information available about all Assay Modules in the network.</p>
</section> -->

<form class="w-full sm:w-auto">
    <div class="form-fields !gap-0">
        <div class="field">
            <div class="input-wrapper !py-1 !pr-1">
                <input id="fakeSearch" type="text" placeholder="Search..." name="s" value="<?= $filters['s'] ?? "";?>">
                <button id="fakeSearchBtn" class="cursor-pointer aspect-square rounded-full p-2 bg-dark transition-all duration-500 hover:scale-110 hover:opacity-75">
                    <svg class="h-4 w-auto fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
                    </svg>
                </button>
            </div>
        </div>
        <?php if($filters) : ?>
            <a href="<?= strtok($currentURL, '?');?>" id="removeFilter" class="p-2 transition-all duration-500 hover:scale-110 hover:opacity-75 tooltip" title="Clear Filters">
                <svg class="h-7 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
                </svg>
            </a>
        <?php endif;?>
    </div>
</form>

<table id="instrumentsTable">
    <thead>
        <th>
            <a href="<?= updateQueryString(["sp" => "serial_number", "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == "serial_number") || ($filters['sd'] ?? "empty") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                <span>Serial No</span>
                <svg class="h-4 fill-dark <?= (isset($filters['sp']) && $filters['sp'] == "serial_number") ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                </svg>
            </a>
        </th>
        <th>
            <a href="<?= updateQueryString(["sp" => "front_panel_id", "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == "front_panel_id") || ($filters['sd'] ?? "desc") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                <span>Panel ID</span>
                <svg class="h-4 fill-dark <?= (isset($filters['sp']) && $filters['sp'] == "front_panel_id") ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                </svg>
            </a>
        </th>
        <th class="hidden xs:table-cell">
            <a href="<?= updateQueryString(["sp" => "status", "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == "status") || ($filters['sd'] ?? "desc") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                <span>Status</span>
                <svg class="h-4 fill-dark <?= (isset($filters['sp']) && $filters['sp'] == "status") ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                </svg>
            </a>
        </th>
        <th></th>
    </thead>

    <tbody id="instrumentsTableBody">

    </tbody>
</table>

<div id="instrumentModalWrapper" class="modal-wrapper"></div>

<script type="module" src="/js/instrumentCheck.js"></script>