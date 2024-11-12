document.addEventListener('DOMContentLoaded', () => {
    // Include the options for configuring the calendar for the date range picker
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

    // Initialize all date range picker elements
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
                };
            }

            // Create the calendar
            const dateRangePicker = new VanillaCalendar('#' + dateRangePickerDiv.id, options);
            dateRangePicker.init();
        });
    }

    // Function to update the input value with selected date and time
    function updateInput(e, self) {
        var dates = self.selectedDates[0] ?? new Date().toLocaleDateString("fr-CA", {year:"numeric", month: "2-digit", day:"2-digit"});
        var time = self.selectedTime ?? '00:00';

        self.HTMLInputElement.value = dates + ' ' + time;
    }

    // Options for the date time picker
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

    // Initialize all date time picker elements
    var dateTimePickers = document.querySelectorAll('.date-time-picker');
    if (dateTimePickers) {
        dateTimePickers.forEach((dateTimePickerDiv) => {

            var newOptions2 = options2;

            // Get specific pre-filled items for that date time picker
            if (dateTimePickerDiv.value) {
                var dateTime = dateTimePickerDiv.value.split(" ");
                if (dateTime.length === 2) {
                    var date = dateTime[0];
                    var time = dateTime[1];
                    newOptions2.settings.selected = {
                        dates: [date],
                        time: time,
                    };
                }
            }

            // Create the calendar for the date time picker
            const dateTimePicker = new VanillaCalendar('#' + dateTimePickerDiv.id, options2);
            dateTimePicker.init();
        });
    }

        // Include the options for configuring the date picker
        const optionsDatePicker = {
            input: true,
            settings: {
                selection: {
                    day: 'single',  // Single day selection
                },
                visibility: {
                    daysOutside: false,
                    weekend: false,
                },
            },
            actions: {
                changeToInput(e, self) {
                    if (!self.HTMLInputElement) return;
                    if (self.selectedDates[0]) {
                        self.HTMLInputElement.value = self.selectedDates[0];
                    } else {
                        self.HTMLInputElement.value = '';
                    }
                },
            },
        };

        // Initialize all date picker elements
        var datePickers = document.querySelectorAll('.date-picker');
        if (datePickers) {
            datePickers.forEach((datePickerDiv) => {

                var newOptionsDatePicker = optionsDatePicker;

                // See if it already has a value!
                if (datePickerDiv.value) {
                    // Get the date
                    var date = datePickerDiv.value;
                    newOptionsDatePicker.settings.selected = {
                        dates: [date],
                        month: date.split("-")[1] - 1,
                        year: date.split("-")[0],
                    };
                }

                // Create the calendar for the date picker
                const datePicker = new VanillaCalendar('#' + datePickerDiv.id, newOptionsDatePicker);
                datePicker.init();
            });
        }
});
