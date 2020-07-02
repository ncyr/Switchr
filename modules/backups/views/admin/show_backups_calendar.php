<div class="backupsCal">
	<?php $maxRows = 10; $this->load->library('calendar'); ?>
	<?=$this->calendar->generate($year, $month, $eventDates)?>
</div>
<div class="backupsHist">
	<h4>Recent History</h4>
	<table>
		<?php if(isset($stores) && is_dir(FCPATH.UPLOAD_PATH. 'backups/'.$this->session->userdata('current_store')))
		{
			if($backups = @$this->Backup_model->showBackups($this->session->userdata('current_store')))
			{
				$count = count($backups);
				//if they have less than the maxcount then just make it the count
				if($count <= $maxRows){
					$maxRows = ($count-1);
				}
				if($backups[0] != '')
				{
					for($i=0; $i<=$maxRows; $i++)
					{
						$files = scandir(FCPATH.UPLOAD_PATH . 'backups/' . $this->session->userdata('current_store').'/'.$backups[$i]);
						$fileCount = count($files);
						echo '<tr><td><label for="backupDate">Date: </label><a name="backupDate" href="/admin/backups/show_day/'. $backups[$i] .'" id="' . $backups[$i] . '">' . $backups[$i] . '</a>&nbsp;&nbsp;&nbsp;&nbsp;<label for="lastAttempt">Last Update: </label>' . date("F j, Y, g:i a ", filemtime(FCPATH.UPLOAD_PATH . 'backups/' . $this->session->userdata('current_store') .'/'.$backups[$i].'/'.$files[($fileCount-2)])) . '</td>
						<td><input id="updateBackupBtn" title="'.$backups[$i].'" type="submit" value="Update" class="btn orange edit confirm" /></td></td><td><input id="removeBackupBtn" name="' . $backups[$i] . '" title="' . $backups[$i] . '" type="submit" class="btn red delete" value="Remove"/></td>
						</tr>';
					}
				}
				else{
					echo '<div class="no_data">I see that your a user with a backup folder, but no backups are found</div>';
				}
			}
			else{
				echo '<div class="no_data">No Backups Found!</div>';
			}
		}
		else
		{
			echo '<div class="no_data">No Stores Found!</div>';
		} ?>
	</table>
</div>
