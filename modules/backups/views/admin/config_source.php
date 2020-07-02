<div id="content-body">
	<section class="title">
        <h2>Configure Source <?=$sourceId?></h2>
    </section>  
    <section class="item">
        <fieldset>
		<legend>Edit</legend>
        <?=form_open('/admin/backups/config_source/'. $sourceId)?>
            <label for='backup_type'>Source Type:</label><?=form_dropdown('backup_type', array('ftp'=>'FTP', 'sftp'=>'SFTP', 'posignite'=>'POSignite'))?><br/>
            <label for='username'>Username:</label><?=form_input('username', set_value('username', $source->username));?><br/>
            <label for='password'>Password:</label><?=form_password('password', set_value('password', $this->encrypt->decode($source->password)));?><br/>
            <label for='hostname'>Hostname:</label><?=form_input('hostname', set_value('hostname', $source->hostname));?><br/>
            <label for='hostname'>Port:</label><?=form_input('port', set_value('port', $source->port));?><br/><br/>
            <label for='passive'>Passive Mode:</label>Yes&nbsp;
			<?php
				if($source->passive == '1'){
					echo form_radio('passive', '1', TRUE);
					echo 'No&nbsp;';
					echo form_radio('passive', '0');
				}
				else{
					echo form_radio('passive', '1');
					echo 'No&nbsp;';
					echo form_radio('passive', '0', TRUE);
				}
			?>
			<br/>
            <br/>
            <label for='backup_location'>Location (ex: 'C:/Aloha'):</label><?=form_input('backup_location', set_value('backup_location', $source->backup_location));?><br/>
            <label for='destination'>Destination:</label><?=form_input('destination', set_value('destination', $source->destination));?><br/>
            <br/>
            <label for='user_cert'>SSH Public Key (SFTP Only):</label><?=form_input('user_cert', set_value('user_cert', $this->encrypt->decode($source->user_cert)));?><br/>
            <label for='user_cert_pass'>SSH Public Key Password (SFTP Only):</label><?=form_password('user_cert_pwd', set_value('user_cert_pass', $this->encrypt->decode($source->user_cert_pwd)));?><br/>
            <?=form_submit('submitBtn', 'Save', 'class="btn green"')?><?=form_submit('cancelBtn', 'Cancel', 'onClick="history.go(-1)" class="btn red"')?>
        </fieldset>
    </section>
</div>
