<style>
    button, input, optgroup, select, textarea
    {
        padding: 5px;
        width: 99%;
        font-size: 20px;
        font-family: 'Raleway', sans-serif;
    }
    .col-sm-8
    {
        padding: 60px 35px 0 35px;
    }

    /* Center the dialog buttons */
    div.ui-dialog-buttonset {
        text-align: center;
        width: 100%;
    }

    /* Show 2 buttons on one row (side-by-side) */
    div.ui-dialog-buttonpane.ui-widget-content.ui-helper-clearfix > div > button {
        max-width: 45%;
    }

    table tr:nth-of-type(n+2) {
        display:none;
    }
</style>
<div id="body" class="section">
            {{ if user:logged_in }}
            <div class="panel panel-default create-edit-backup">
                <div class="panel-heading">
                    <h2>{{ template:title }}</h2>
                </div>
            <table class="table table-striped datatable">
                <form id="backup-create-form" action="/backups/create/<?php echo "$type/$host_id";?>" method="post" enctype="multipart/form-data">
                    <div class="table-responsive">
                        <table class='table'>
                            <input type="hidden" name="backup_dest_host_id" id="backup_dest_host_id" value=<?php echo $host_id; ?>>
                            <input type="hidden" name="backup_dest_type" id="backup_dest_type" value="">
                            <tr class="even">
                                <td class="col-xs-12">
                                    <div id="ftp-or-sftp" style="">
                                        <div style="display:inline-block;">
                                            <input name="backup_dest_type_button" id="ftp-radio" type="button" value="FTP" class="btn">
                                        </div>

                                        <div style="display:inline-block;" id="sftp-radio-div">
                                            <input name="backup_dest_type_button" id="sftp-radio" type="button" value="SFTP" class="btn">
                                        <div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="odd both">
                                <td class="col-xs-12">
                                    <label for="backup_dest_name">Display Name:</label>
                                    <input name="backup_dest_name" id="backup_dest_name" type="text" maxlength="50">
                                </td>
                            </tr>
                            <tr class="even both">
                                <td class="col-xs-12">
                                    <input type="hidden" name="backup_dest_uploadat" id="cronString">
                                    <label for="backup_dest_uploadat">When to upload:</label>
                                    <div class="upload-at"></div>
                                </td>
                            </tr>
                            <tr class="odd both">
                                <td class="col-xs-12">
                                    <div class="col-xs-6">
                                        <label for="backup_dest_username">FTP Username:</label>
                                        <input name="backup_dest_username" id="backup_dest_username" type="text" maxlength="25">
                                    </div>
                                    <div class="col-xs-6">
                                        <label for="backup_dest_password">FTP Password:</label>
                                        <input name="backup_dest_password" id="backup_dest_password" type="password">
                                    </div>
                                </td>
                            </tr>
                            <tr class="even both">
                                <td class="col-xs-12">
                                    <div class="col-xs-6">
                                        <label for="backup_dest_hostname">FTP Hostname or IP Address:</label>
                                        <input name="backup_dest_hostname" id="backup_dest_hostname" type="text" maxlength="50">
                                    </div>
                                    <div class="col-xs-6">
                                        <label for="backup_dest_port">FTP Port:</label>
                                        <input type="number" name="backup_dest_port" id="backup_dest_port" value="21" maxlength="5">
                                    </div>
                                </td>
                            </tr>
                            <tr class="odd ftp">
                                <td class="col-xs-12">
                                    <label for="backup_dest_passive">Passive or Active Mode (Passive is suggested):</label>
                                    <select name="backup_dest_passive" id="backup_dest_passive">
                                        <option value="0">Passive</option>
                                        <option value="1">Active</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="even sftp">
                                <td class="col-xs-12">
                                    <label for="backup_dest_ssh_key">Paste Key (if using SFTP):</label>
                                    <textarea name="backup_dest_ssh_key" cols="40" rows="10" id="backup_dest_ssh_key" value=""></textarea>
                                </td>
                            </tr>
                            <tr class="odd sftp">
                                <td class="col-xs-12">
                                    <label for="backup_dest_ssh_password">SSH Key Password (if used):</label>
                                    <input name="backup_dest_ssh_password" id="backup_dest_ssh_password" type="password">
                                </td>
                            </tr>
                            <tr class="even both">
                                <td class="col-xs-12">
                                    <label for="backup_dest_dest">FTP Destination Folder Path:</label>
                                    <div class="col-xs-6">
                                        <button id="ftp-open">Choose...</button>
                                    </div>
                                    <div class="col-xs-6">
                                        <input name="backup_dest_dest" id="backup_dest_dest" type="text" readonly="readonly" style="background-color:rgb(235, 235, 228);">
                                    </div>
                                </td>
                            </tr>
                            <tr class="odd both">
                                <td class="col-xs-12">
                                    <label for="backup_dest_source">Folder or File(s) to Backup:</label>
                                    <input type="hidden" name="backup_dest_source" id="backup_dest_source">
                                    <div class="col-xs-6">
                                        <button id="source-open">Choose...</button>
                                    </div>
                                    <div class="col-xs-6">
                                        <button id="source-selected-open">View Selected Files/Folders</button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="even both">
                                <td class="col-xs-12">
                                    <label for="backup_dest_status">Status:</label>
                                    <select name="backup_dest_status" id="backup_dest_status">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="odd both">
                                <td class="col-xs-12" style="text-align:center;">
                                    <input id="backup-submit" type="submit" value="Save">
                                </td>
                            </tr>
                        </table>
                    </form>
                    <p>{{ error }}{{ input }}</p>
           <p>{{ error }}{{ input }}</p>
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
        </div>
    </div>
</div>

<div id="dialog" style="display: none;" title="">
    <p></p>
</div>

<div id="ftp-client" style="display: none;" title="Select folder for upload">
    <ul id="ftp-folders">
        <li id="ftp-folder-up">..</li>
    </ul>
</div>

<div id="ftp-mkdir" title="Make directory">
    <input name="ftp-new-dir" type="text" id="ftp-new-dir" style="display:none;">
</div>

<div id="source-client" style="display: none;" title="2nd checkbox will make recursive.">
    <ul id="source-folders">
        <li id="source-folder-up">..</li>
    </ul>
</div>

<div id="source-selected" style="display: none;" title="Tick checkbox to make recursive">
    <ul id="source-selected-list" style="list-style:none;padding-left:0px;">
    </ul>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        // turn the div into a cron editor
        $('.upload-at').croneditor({
            value: "* * * * *"
        });
    });
</script>
