jQuery(document).ready(function($) {
    // If startDate is 'Today' then we don't want the user messing with endDate, even though it's moot.
    // Allow the user to switch between 'Today' and date ranges.
    $('[name=endDate]').attr('disabled', true);
    $('[name=startDate]').click(function() {
        if ($('[name=startDate]').val() != 'Today') {
            $("[name=endDate] option[value='Today']").remove();
            $('[name=endDate]').attr('disabled', false);
        } else {
            $("[name=endDate]").append('<option value="Today">Today</option>');
            $('[name=endDate]').val('Today');
            $('[name=endDate]').attr('disabled', true);
        }
    });

	$("#startDate").datepicker({ dateFormat: 'yymmdd' });
	$("#endDate").datepicker({ dateFormat: 'yymmdd' });

	$('[name=rptSubmitBtn]').click(function(e){
		e.preventDefault();
        $.dialog({
            title: 'Please Wait.',
            content: 'Please wait a moment while we get the report.',
            closeIcon: false,
            backgroundDismiss: false,
            escapeKey: false,
        });
		var host_id = $('[name=host_id]').val();
		var mod = $('[name=mod]').val();
		var type = $('[name=reportSetting]').val();
		var startDate = $('[name=startDate]').val();
		var endDate = $('[name=endDate]').val();
		var sendTo = $('#sendTo').val();
		var sendToNum = $('#sendToNum').val();
		//var reportName = $('[name = "reportSetting"] option:selected').html();

        // $('#reportContent').html('<div style="text-align:center;"><img class="loading" style="width:111px;" src="/addons/shared_addons/themes/switchr/img/loading.gif"></div>');

        // $.colorbox({overlayClose:false, escKey:false, width:"400px", html:'<h1 style="text-align:center; font-size: 18px">- Please Wait -</h1><img style="float:left;" src="/images/loading.gif"><p style="float:left; width:260px; margin-left: 20px; margin-top:0; font-size: 14px;">Obtaining a live report from the store. Connection speeds may vary. <br><span style="font-size:11px; font-weight: bold">This dialog will automatically close when completed. </span></p>'});
		// $('#cboxClose').css('display', 'none');

        // sendToNum needs to be last in case of falsy value, which will corrupt the whole line.
		$('#reportContent').load('/reports/ajax_show_doc/' + host_id + '/' + mod + '/' + type + '/' + startDate + '/' + endDate + '/' + sendTo + '/' + sendToNum, function() {
			$('.jconfirm').remove();
		});
	});

    $('.date-picker').datepicker( {
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'mm-yy',
        onClose: function(dateText, inst) {
            $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
        }
    });

    $('.date-picker_month').datepicker( {
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'yy',
        onClose: function(dateText, inst) {
            $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            $('#statement_content').load('/reports/ajax_statements/'+inst.selectedYear);
        }
    });

    // Display the field to input receiving phone number.
    $("#sendTo").change(function() {
	      	var str = $(this).val();
			if (str == "sms") {
                $("#smsphone").show()
			}
			else {
				$('#smsphone').hide();
			}
    });
    //report debug info
    $('[name=dbgSubmitBtn]').click(function(e){
		e.preventDefault();
        $.dialog({
            title: 'Please Wait.',
            content: 'Please wait a moment while we get the report.',
            closeIcon: false,
            backgroundDismiss: false,
            escapeKey: false,
        });
		var host_id = $('[name=host_id]').val();
		var mod = $('[name=mod]').val();
		var filename = $('[name=filename]').val();
		//var reportName = $('[name = "reportSetting"] option:selected').html();

        // $('#reportContent').html('<div style="text-align:center;"><img class="loading" style="width:111px;" src="/addons/shared_addons/themes/switchr/img/loading.gif"></div>');

        // $.colorbox({overlayClose:false, escKey:false, width:"400px", html:'<h1 style="text-align:center; font-size: 18px">- Please Wait -</h1><img style="float:left;" src="/images/loading.gif"><p style="float:left; width:260px; margin-left: 20px; margin-top:0; font-size: 14px;">Obtaining a live report from the store. Connection speeds may vary. <br><span style="font-size:11px; font-weight: bold">This dialog will automatically close when completed. </span></p>'});
		// $('#cboxClose').css('display', 'none');

        // sendToNum needs to be last in case of falsy value, which will corrupt the whole line.
		$('#reportContent').load('/reports/getDebugFile/' + host_id + '/' + mod + '/' + encodeURI(filename), function() {
			$('.jconfirm').remove();
		});
	});
});
