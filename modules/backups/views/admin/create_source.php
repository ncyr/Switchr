
<div id="content-body">
	<section class="title">
        <h2>Configure Source</h2>
    </section>  
    <section class="item">
        <fieldset>
		<legend>Edit</legend>
        <?=form_open('/admin/backups/create_source')?>
            <label for='backup_type'>Source Type:</label><?=form_dropdown('backup_type', array('ftp'=>'FTP', 'sftp'=>'SFTP', 'posignite'=>'POSignite'))?><br/>
            <label for='username'>Username:</label><?=form_input('username');?><br/>
            <label for='password'>Password:</label><?=form_password('password');?><br/>
            <label for='hostname'>Hostname:</label><?=form_input('hostname');?><br/>
            <label for='hostname'>Port:</label><?=form_input('port');?><br/><br/>
            <label for='passive'>Passive Mode:</label>Yes&nbsp;<?=form_radio('passive', '1', TRUE);?>No&nbsp;<?=form_radio('passive', '0');?><br/>
            <br/>
            <label for='backup_location'>Location (ex: 'C:/Aloha'):</label><?=form_input('backup_location');?><br/>
            <label for='destination'>Destination:</label><?=form_input('destination');?><br/>
            <label for='limit'>Limit:</label><?=form_input('limit');?><br/>
            <br/>
            <label for='user_cert'>SSH Public Key (SFTP Only):</label><?=form_input('user_cert');?><br/>
            <label for='user_cert_pass'>SSH Public Key Password (SFTP Only):</label><?=form_input('user_cert_pwd');?><br/>
            <?=form_submit('submitBtn', 'Save')?><?=form_submit('cancelBtn', 'Cancel', 'onClick="history.go(-1)"')?>
        </fieldset>
    </section>
</div>
