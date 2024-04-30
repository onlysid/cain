document.addEventListener('DOMContentLoaded', () => {
    // Include the options for configuring the calendar
    const options = {
        input: true,
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
                    self.HTMLInputElement.value = `${self.selectedDates[0]} - ${self.selectedDates[self.selectedDates.length - 1]}`;
                } else if (self.selectedDates[0]) {
                    self.HTMLInputElement.value = self.selectedDates[0];
                } else {
                    self.HTMLInputElement.value = '';
                }
            },
        },
    };

    var dateRangePickers = document.querySelectorAll('.date-range-picker');

    if(dateRangePickers) {
        dateRangePickers.forEach((dateRangePickerDiv) => {

            var newOptions = options;

            // Get specific pre-filled items for that datepicker
            if(dateRangePickerDiv.dataset.multiple) { 
                newOptions.type = 'multiple';
            }

            // See if it already has a value!
            if(dateRangePickerDiv.value) {
                // Get the dates
                var dates = dateRangePickerDiv.value.split(" - ");

                newOptions.settings.selected = {
                    dates: [dates[0] + ":" + dates[1]],
                    month: dates[0].split("-")[1] - 1,
                    year: dates[0].split("-")[0],
                }
            }
            

            // Create the calendar
            const dateRangePicker = new VanillaCalendar('#' + dateRangePickerDiv.id, options);
        
            // If the daterange picker exists, initialise it.
            dateRangePicker.init();
        })
    }

});