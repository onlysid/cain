
<?php // QC Settings

$hospitalInfo = systemInfo();

// Extract 'name' as keys and 'value' as values
$settings = array_column($hospitalInfo, 'value', 'name');

// Hospital Info Settings Subset
$qcKeys = ['qc_enforcement', 'qc_positive_requirements', 'qc_negative_requirements', 'qc_enable_independence'];
$qcSettings = array_intersect_key($settings, array_flip($qcKeys));
?>

<h1>Backup/Export</h1>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>Save results to a CSV file and, optionally, delete them afterwards.</p>
</section>

<link href="/js/vanilla-calendar/build/vanilla-calendar.min.css" rel="stylesheet">
<script src="/js/vanilla-calendar/build/vanilla-calendar.min.js" defer></script>

<p class="text-center">Here's an example of a calendar.... nice!</p>

<form class="form">
    <div class="form-fields">
        <div class="input-wrapper max-w-sm">
            <svg class="cursor-pointer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"/>
            </svg>
            <input id="calendar" type="text" placeholder="Select Dates" readonly/>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const options = {
            input: true,
            type: 'multiple',
            settings: {
                selection: {
                    day: 'multiple-ranged',
                },
                visibility: {
                    daysOutside: false,
                    weekend: false,
                },
            },
            date: {
                max: new Date().toJSON().slice(0, 10)
            },
            actions: {
                changeToInput(e, self) {
                if (!self.HTMLInputElement) return;
                if (self.selectedDates[1]) {
                    self.selectedDates.sort((a, b) => +new Date(a) - +new Date(b));
                    self.HTMLInputElement.value = `${self.selectedDates[0]} — ${self.selectedDates[self.selectedDates.length - 1]}`;
                } else if (self.selectedDates[0]) {
                    self.HTMLInputElement.value = self.selectedDates[0];
                } else {
                    self.HTMLInputElement.value = '';
                }
                },
            },
        };

        const calendar = new VanillaCalendar('#calendar', options);
        calendar.init();
    });
</script>