jQuery(function() {
    /* Japanese initialisation for the jQuery UI date picker plugin. */
    jQuery.datepicker.regional['ja'] = {
        closeText: '閉じる',
        prevText: '&#x3c;前',
        nextText: '次&#x3e;',
        currentText: '今日',
        monthNames: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
        monthNamesShort: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
        dayNames: ['日曜日','月曜日','火曜日','水曜日','木曜日','金曜日','土曜日'],
        dayNamesShort: ['日','月','火','水','木','金','土'],
        dayNamesMin: ['日','月','火','水','木','金','土'],
        weekHeader: '週',
        dateFormat: 'yy-mm-dd',
        firstDay: 0,
        isRTL: false,
        showMonthAfterYear: true,
        yearSuffix: '年'
    };
    jQuery.datepicker.setDefaults(jQuery.datepicker.regional['ja']);

    jQuery.timepicker.regional['ja'] = {
        currentText: '現在',
        closeText: '閉じる',
        amNames: ['AM', 'A'],
        pmNames: ['PM', 'P'],
        timeFormat: 'HH:mm',
        timeSuffix: '',
        timeOnlyTitle: '時間選択',
        timeText: '時間',
        hourText: '時',
        minuteText: '分',
        secondText: '秒',
        millisecText: 'ミリ秒',
        timezoneText: 'タイムゾーン',
        isRTL: false
    };
    jQuery.timepicker.setDefaults(jQuery.timepicker.regional['ja']);

    var startDateTextBox = jQuery('#start_date');
    var endDateTextBox = jQuery('#end_date');

    startDateTextBox.datetimepicker({
        altField: '#start_time',
        controlType: 'select',
        stepMinute: 10,
        onClose: function(dateText, inst) {
            if (endDateTextBox.val() != '') {
                var testStartDate = startDateTextBox.datetimepicker('getDate');
                var testEndDate = endDateTextBox.datetimepicker('getDate');
                if (testStartDate > testEndDate)
                    endDateTextBox.datetimepicker('setDate', testStartDate);
            } else {
                endDateTextBox.val(dateText);
            }
        },
        onSelect: function(selectedDateTime) {
            endDateTextBox.datetimepicker('option', 'minDate', startDateTextBox.datetimepicker('getDate'));
        }
    });

    endDateTextBox.datetimepicker({
        altField: '#end_time',
        controlType: 'select',
        stepMinute: 10,
        onClose: function(dateText, inst) {
            if (startDateTextBox.val() != '') {
                var testStartDate = startDateTextBox.datetimepicker('getDate');
                var testEndDate = endDateTextBox.datetimepicker('getDate');
                if (testStartDate > testEndDate)
                startDateTextBox.datetimepicker('setDate', testEndDate);
            } else {
                startDateTextBox.val(dateText);
            }
        },
        onSelect: function(selectedDateTime) {
            startDateTextBox.datetimepicker('option', 'maxDate', endDateTextBox.datetimepicker('getDate'));
        }
    });

});
