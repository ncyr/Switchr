<?php echo form_open("admin/servers/upload_key")?>

    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>
    <label>Select a Server</label>
    <select name="server_id" >
        
        <?php foreach($server_list['entries'] as $server):?>
        <option value="<?php echo $server['id'] ?>"><?php echo $server['server_name'] ?></option>
        <?php endforeach; ?>
    </select>
    <br>
    <label>Key Data</label>
    <textarea name="key_data">
        
    </textarea>
    <input type="submit" value="Submit" name="submitBtn">
<?php echo form_close()?>