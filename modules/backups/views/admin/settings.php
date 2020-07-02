
<section class="title">
    <h4>Backup Settings</h4>
</section>
<section class="item">
	<?php if (false !== ($storelist = $stores->result()) && count($storelist) > 1): ?>
	    <span>
	        <select id="report_store">
	            <option value="">-- Change Location --</option>
	            <?php foreach ($storelist as $store): ?>
	                <option value="<?=$store->id?>"><?=$store->store_name?></option>
	            <?php endforeach ?>
	        </select>
	    </span>
	<?php endif ?><br/>
	<h4>Choose Backup Types</h4>
		<form action="">
			<table>
				<tr>
					<td>
						<label for="backup-pi">Backup to POSignite Servers:&nbsp;</label></td><td>
						<input id="backup-pi" type="checkbox">
					</td>
				</tr>
				<tr>
					<td>
						<label for="backup-ext">Backup to External Source:&nbsp;</label></td><td>
						<input id="backup-ext" type="checkbox">
					</td>
				</tr>
			</table>
		</form>
		<br/>
		<br/>
		<h4>External Source Configuration</h4>
		<form action="">
			<table>
				<tr>
					<td>
						<label for="ftp-option">Connection Type (SFTP/FTP):&nbsp;</label></td><td>
						<select id="ftp-option">
							<option value="ftp">FTP</option>
							<option value="sftp">SFTP</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<label for="hostname">Hostname:&nbsp;</label></td><td>
						<input id="hostname" type="text">
					</td>
				</tr>
				<tr>
					<td>
						<label for="port">Port:&nbsp;</label></td><td>
						<input id="port" type="text">
					</td>
				</tr>
				<tr>
					<td>
						<label for="username">Username:&nbsp;</label></td><td>
						<input id="username" type="text">
					</td>
				</tr>
				<tr>
					<td>			
						<label for="password">Password:&nbsp;</label></td><td>
						<input id="password" type="password">
					</td>
				</tr>
				<tr>
					<td>
						<label for="default-path">Storage Path:&nbsp;</label></td><td>
						<input id="default-path" type="text">
					</td>
				</tr>
				<tr>
					<td>
						<label for="ssh-key">SSH Key (SFTP only):&nbsp;</label></td><td>
						<input id="ssh-key" type="file">
					</td>
				</tr>
				<tr>
					<td>
						<label for="pos-path">POS Server Path1:<br/>(no trailing slash)</label></td><td>
						<input id="pos-path" type="text">
					</td>
				</tr>
				<tr>
					<td>
						<label for="pos-path">POS Server Path2:<br/>(no trailing slash)</label></td><td>
						<input id="pos-path" type="text">
					</td>
				</tr>															
			</table>
			<br/></br>
			<a href="" class="btn green">Submit Configuration</a>&nbsp;<a href="" class="btn red">Cancel</a>
		</form>
</section>

			
			

			
			