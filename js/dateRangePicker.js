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

    function updateInput(e, self) {
        var dates = self.selectedDates[0] ?? new Date().toLocaleDateString("fr-CA", {year:"numeric", month: "2-digit", day:"2-digit"});
        var time = self.selectedTime ?? '00:00';

        self.HTMLInputElement.value = dates + ' ' + time;
    }

    const options2 = {
        input: true,
        actions: {
            changeToInput(e, self) {
                if (!self.HTMLInputElement) return;
                updateInput(e, self);
            },
            changeTime(e, self) {
                if (!self.HTMLInputElement) return;
                updateInput(e, self);
            }
        },
        settings: {
            visibility: {
                positionToInput: 'center',
            },
            selection: {
                time: 24,
            },
        },
    };

    // If there is an element with dateTimePicker, load this.
    if(document.querySelector('#dateTimePicker')) {
        const calendarInput = new VanillaCalendar('#dateTimePicker', options2);
        calendarInput.init();
    }
});