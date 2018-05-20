$(function(){
    'use strict';

    $('#link-homepage').addClass('active');

    $('.select2').select2({
        minimumResultsForSearch: Infinity
    });

    var months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July',
        'August', 'September', 'October', 'November', 'December'];

    var date = new Date();
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    var modalYear = $('#date_form_year');
    var modalMonth = $('#date_form_month');
    var modalMonthName = $('#create_month_form_monthName');
    var yearOption = $('<option>', {'value': year});
    var nextYearOption = $('<option>', {'value': year + 1});

    yearOption.text(year);
    nextYearOption.text(year + 1);

    if(month === 12) {
        yearOption.attr('disabled', 'disabled');
    }

    modalYear.append(yearOption).append(nextYearOption);

    var selectedYear = modalYear.val();

    generateMonthsOptions();
    modalYear.change(function() {
        selectedYear = $(this).val();
        modalMonth.empty();
        generateMonthsOptions();
    });

    function generateMonthsOptions() {
        for(var i = 1; i <= 12; i++) {
            var monthOption = $('<option>', {'value': i});
            monthOption.text(months[i]);

            if (i < month && selectedYear == year || dateExists(selectedYear, i, userDates)) {
                monthOption.attr('disabled', 'disabled');
            }

            modalMonth.append(monthOption);
        }
    }

    function dateExists(year, month, userDates) {
        for(var i = 0; i < userDates.length; i++) {
            if(userDates[i][0] == year && userDates[i][1] == month) {
                return true;
            }
        }
        return false;
    }


    var selectedMonth = modalMonth.val();
    modalMonthName.val(months[selectedMonth]);
    modalMonth.change(function() {
        selectedMonth = $(this).val();
        modalMonthName.val(months[selectedMonth]);
    });

});