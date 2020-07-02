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
});
</script>
	

<div id="confirm" title="Confirm Action" style="display:none"><p>Please confirm your action.</p></div>
<div id="content-body">
	<section class="title">
        <h2>Recent History</h2>
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
		<legend>Archived Sources</legend>
        <table>
		    <thead>
				<th><a onclick="history.go('-1')">..</a></th>
				<th>File</th>
				<th>Size</th>
				<th>Received</th>
				<th></th>
				<th></th>
            </thead>
		<?php if(isset($storesByOwner) && is_dir(FCPATH.UPLOAD_PATH. 'backups/'.$currentStore.'/'. $sourceId .'/'.$location))
		{
			if($files)
			{
				$count = count($files);
				$count = ($count-1);
				if($files[0] != '')
				{
					for($i=0; $i<=$count; $i++)
					{
						//$files = scandir(FCPATH.UPLOAD_PATH . 'backups/' . $this->session->userdata('current_store').'/'.$backups[$i]);
						//$fileCount = count($files);
						echo '<tr><td>';
						if(is_dir(FCPATH.UPLOAD_PATH. 'backups/'.$currentStore.'/'. $sourceId .'/'.$location.'/'.$files[$i])){ echo "Folder"; } else{ echo "File";}
						echo '</td><td>';
						if(is_dir(FCPATH.UPLOAD_PATH. 'backups/'.$currentStore.'/'. $sourceId .'/'.$location.'/'.$files[$i])){
							echo '<a name="backupDate" href="/admin/backups/show_backups/'. $sourceId .'/' . $files[$i] . '" id="' . $files[$i] . '">' . $files[$i] . '</a>';
						}
						else{
							echo $files[$i];
						}
						echo '
							<td>
								' . filesize(FCPATH.UPLOAD_PATH . 'backups/' . $currentStore .'/'. $sourceId . '/' . $location .'/'.$files[$i]) . '
							</td>
							<td>' . date("F j, Y, g:i a ", filemtime(FCPATH.UPLOAD_PATH . 'backups/' . $currentStore .'/'. $sourceId . '/' . $location .'/'.$files[$i])) . '</td>
							<td>
								<input id="updateBackupBtn" title="'.$files[$i].'" type="submit" value="Update" class="btn orange edit confirm" />
							</td>
							<td>
								<input id="removeBackupBtn" name="' . $files[$i] . '" title="' . $files[$i] . '" type="submit" class="btn red delete" value="Remove"/>
							</td>
						</tr>';
					}
				}
				else{
					echo '<div class="no_data">I see that your a user with a backup folder, but no backups are found</div>';
				}
			}
			else{
				echo '<div class="no_data">' . lang('backups:no_backups') . '</div>';
			}
		}
		else
		{
			echo '<div class="no_data">Nothing found!</div>';
		} ?>
	</table>
        </fieldset>
        </div>
    </section>
</div>
