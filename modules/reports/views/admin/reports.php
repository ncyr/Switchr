<script type="text/javascript">

jQuery(document).ready(function($) {
	$("#report_store").change(function(){
		
		var storeId = $(this).val();
		$.ajax({
			url: "/admin/hosts/store_change/" + storeId,
			success: function(){
				alert('Store Changed');
			}
			});
	});

	$("#startDate").datepicker({ dateFormat: 'yymmdd' });
	$("#endDate").datepicker({ dateFormat: 'yymmdd' });

	$('[name=rptSubmitBtn]').click(function(e){
		e.preventDefault();
		var type = $('[name=report_type]').val();
		var startDate = $('#startDate').val();
		var endDate = $('#endDate').val();

		$('#reportContent').html('<img class="loading" src="/images/loading.gif">Loading...');
		// $.colorbox({overlayClose:false, escKey:false, width:"400px", html:'<h1 style="text-align:center; font-size: 18px">- Please Wait -</h1><img style="float:left;" src="/images/loading.gif"><p style="float:left; width:260px; margin-left: 20px; margin-top:0; font-size: 14px;">Obtaining a live report from the store. Connection speeds may vary. <br><span style="font-size:11px; font-weight: bold">This dialog will automatically close when completed. </span></p>'});
		// $('#cboxClose').css('display', 'none');

		$('#reportContent').load('/admin/reports/ajax_show_doc/' + type + '/' + startDate + '/' + endDate, function() {
			$.colorbox.close();
		});
	});
});
</script>
<?php if($hosts)
{
	foreach($hosts as $store)
	{
		$storeList[$store->id] = $store->store_name;
	}
} ?>

<div id="content-body">
	<section class="title">
		<h2>Reports</h2>
	</section>
	<section class="item">
		<div class="subheader">
		<?php if ($this->uri->segment(2) != 'hosts' && isset($hosts)): ?>
            <?php if (false !== ($hosts) && count($hosts) > 1): ?>
                <span id="change_store" class="floatLeft">
                	<label for=:"report_store">Change Store</label>
                    <select id="report_store">
                        <option value="">-- Change Location --</option>
                        <?php foreach ($hosts as $store): ?>
                            <option value="<?=$store->id?>"><?=$store->store_name?></option>
                        <?php endforeach ?>
                    </select>
                </span>
                <div class="cleft"></div>
            <?php endif ?>
        <?php endif ?>
        	<div id="reportTypeWrapper" class="floatLeft">
				<label for="report_type">Report Type</label>
				<?=form_dropdown('report_type', array(
					'sales'=>'Sales',
					'pmix'=>'Product Mix',
					'pay'=>'Payments',
					'lbr'=>'Labor',
					'hrs'=>'Hourly Sales and Labor',
					'ebr'=>'Employee Break',
					'tip'=>'Tip Income',
					'foh'=>'Front of House Cash Owed',
					'pd'=>'Payment Detail',
					'void'=>'Voids',
					'srev'=>'Sales By Revenue Center'
				))?>
			</div>
				<div id="date_range" class="floatLeft">
					<div class="floatLeft" style="margin-right:15px">
						<label for="startDate">Start Date</label><br/><input id="startDate" name="startDate" type="text" size="8"/><br/>
					</div>
					<div class="floatLeft">
						<label for="endDate">End Date</label><br/><input id="endDate" name="endDate" type="text" size="8"/>
					</div>
					<input id="button" style="margin:15px 0 0 55px; padding: 10px 35px" type="submit" name="rptSubmitBtn" value="GO"/>
					<div class="cleft"></div>
				</div>
				<div class="cleft"></div>
			</div>
			<hr/>
			<div id="reportContent">
			<?php if(!$hosts): ?>
			<div class="no_data">No hosts found!</div>
			<?php endif ?>
		</div>	
		</div>
	</section>
</div>
