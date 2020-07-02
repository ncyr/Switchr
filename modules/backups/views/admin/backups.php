<script type="text/javascript">
$(function(){
	
	$("#report_store").change(function(){
		var storeId = $(this).val();
		$.ajax({
			url: "/admin/stores/store_change/" + storeId,
			success: function(){
				alert('Store Changed');
			}
			});
	});
	
	$('input#updateBackupBtn').click(function(){
	var date = $(this).attr('title');
	$.colorbox({overlayClose:false, escKey:false, width:"400px", html:"<h1 style='text-align:center; font-size: 18px'>- Please Wait -</h1><p style='font-size: 20px;'>Obtaining a live backup. Connection speeds may vary.</p>"});
	$('#cboxClose').css('display', 'none');
	$('#reportContent').load('/admin/backups/backup_day/'+date, function(){
			$.colorbox.close();
			window.location.reload();
		});
	});
	$('input#removeBackupBtn').click(function(e){
		e.preventDefault();
		var id = $(this).attr('name');
		var confirm = $('#confirm');
		confirm.dialog({
				title: 'Loading - Please Wait',
				maxHeight: 200,
				width: 200,
				modal: true,
				buttons: {
					"Delete Item": function() {
						$.ajax({
						  url: '/admin/backups/remove_backup/'+id, success:
						  function(){
								confirm.html('Removing, Please Wait.');
							  $('body').load('/admin/support','refresh');
						  }
						});
						sleep(2);
						$( this ).dialog( "close" );
					},
					Cancel: function() {
						$( this ).dialog( "close" );
					}
				}
		});
	});
});
// Can't even be bothered to cleanup the HTML in this.
// TODO: Clean this shit.
</script>
<div id="confirm" title="Confirm Action" style="display:none"><p>Please confirm your action.</p></div>
<div id="content-body">
	<section class="title">
        <h2>Backup</h2>
    </section>  
    <section class="item">
		<?php if ($storesByOwner && count($storesByOwner) > 1): ?>
		    <span>
		        <select id="report_store">
		            <option value="">-- Change Location --</option>
		            <?php foreach ($storesByOwner as $store): ?>
		                <option value="<?=$store->id?>"><?=$store->store_name?></option>
		            <?php endforeach ?>
		        </select>
		    </span>
		<?php endif ?>
        <div class="backupsHist">
        <br/>
		
		<fieldset>
			<legend>Backup Settings</legend>
			<table><tr>
			<td>
				<?=form_open('/admin/backups/save_source_cfg')?>
				<label for="posignite">POSignite Cloud</label><?=form_checkbox('posignite');?>
			</td>
			<td>
				<label for="sftp">Your SFTP Server</label><?=form_checkbox('sftp');?>
			</td>
			<td><?=form_submit('saveBtn', 'Save');?></td>
			</tr></table>
			
			
			
		</fieldset>
        <fieldset>
		<legend>POSignite Cloud</legend>
        <table>
            <thead>
				<th>Remote Source</th>
				<th>Destination</th>
				<th>Type</th>
				<th>Limit</th>
                <th>Available</th>
				<th></th>
            </thead>
			
            <?php foreach($backupSources as $source):?>
			<?php if($source->backup_type == 'posignite'):?>
            <tr>
                <td>
					<a href="/admin/backups/show_backups/<?=$source->id?>"><?=$source->backup_location?></a>
				</td>
				<td><?=$source->destination?></td>
				<td><?=$source->backup_type?></td>
				<td><?=$source->limit?></td>
				<td>1524</td>
				<td><a href="/admin/backups/config_source/<?=$source->id?>" class="btn orange">Configure</a></td>
            </tr>
			<?php endif; ?>
            <?php endforeach; ?>
        </table>
        </fieldset>
		<fieldset>
		<legend>SFTP</legend>
        <table>
            <thead>
				<th>Remote Source</th>
				<th>Destination</th>
				<th>Type</th>
				<th>Limit</th>
                <th>Available</th>
				<th></th>
            </thead>
            <?php foreach($backupSources as $source):?>
			<?php if($source->backup_type == 'sftp'):?>
            <tr>
                <td>
					<a href="/admin/backups/show_backups/<?=$source->id?>"><?=$source->backup_location?></a>
				</td>
				<td><?=$source->destination?></td>
				<td><?=$source->backup_type?></td>
				<td><?=$source->limit?></td>
				<td>1524</td>
				<td><a href="/admin/backups/config_source/<?=$source->id?>" class="btn orange">Configure</a></td>
            </tr>
			<?php endif; ?>
            <?php endforeach; ?>
        </table>
        </fieldset>		
        </div>
    </section>
</div>
