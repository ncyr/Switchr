<script type="text/javascript">
jQuery(document).ready(function($){
	$('[name=rptSubmitBtn]').click(function(){
		var type = $('[name=report_type]').val();
		var date = $('input[name=date]').val();
		var live = $('#liveReport').val();

		$.colorbox({overlayClose:false, escKey:false, width:"400px", html:'<h1 style="text-align:center; font-size: 18px">- Please Wait -</h1><img style="float:left;" src="/images/loading.gif"><p style="float:left; width:260px; margin-left: 20px; margin-top:0; font-size: 14px;">Obtaining a live report from the store. This will vary depending on your stores connection and if you have reported this date before. If the data has changed since the last time you reported this date, then check the option "LIVE" when selecting a reporting date. <br><span style="font-size:11px; font-weight: bold">This dialog will automatically close when completed. </span></p>'});
		$('#cboxClose').css('display', 'none');

		$('#reportContent').load('/admin/reports/show/'+type+'/'+date+'/'+live, function(){
			$.colorbox.close();
		});
	});
});
</script>
<?php if($storeResult = $stores->result())
{
	foreach($storeResult as $store)
	{
		$storeList[$store->id] = $store->store_name;
	}
} ?>

<div id="content-body">
	<section class="title">
		<h2>Reports
			<div style="float:right">
				<label for="liveReport">LIVE</label> &nbsp;<?=form_checkbox('liveReport')?>
				<label for="report_type">Report Type</label><?=form_dropdown('report_type', array(
					'wsr'=>'Sales',
					'wpmix'=>'Product Mix',
					'whsl'=>'Weekly Hourly Sales and Labor',
					'srev'=>'Sales By Revenue Center'
				))?>
				<label for="date">Report Date</label><input id="button" name="date" type="text" size="8"/>
				<input id="button" style="margin-top: 40px;" type="submit" name="rptSubmitBtn" value="GO"/>
			</div>
		</h2>
	</section>
	<section class="item">
		<?php
		if(!$storeResult)
		{
			echo '<div class="no_data">No stores found!</div>';
		}
		?>
		<div id="reportContent">
		</div>
	</section>
</div>
