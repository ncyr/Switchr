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
</style>
<div id="body" class="section">
            {{ if user:logged_in }}
            <div class="panel panel-default create-edit-backup">
                <div class="panel-heading">
                	<h2>{{ template:title }}</h2>
                </div>
            <table class="table table-striped datatable">
                <form id="create-s3-form" action="/backups/create/<?php echo "$type/$host_id";?>" method="post" enctype="multipart/form-data">
	                <div class="table-responsive">
	                <table class='table'>
	                	<input type="hidden" name="backup_dest_host_id" id="backup_dest_host_id" value=<?php echo $host_id; ?>>
	                    <input type="hidden" name="backup_dest_type" id="backup_dest_type" value="<?php echo $type;?>">
                        <tr class="odd">
                            <td class="col-xs-12">
                                <label for="backup_dest_name">Display Name:</label>
                                <input name="backup_dest_name" id="backup_dest_name" type="text" maxlength="50">
                            </td>
                        </tr>
                        <tr class="even">
                            <td class="col-xs-12">
                                <input type="hidden" name="backup_dest_uploadat" id="cronString">
                                <label for="backup_dest_uploadat">When to upload:</label>
                                <div class="upload-at"></div>
                            </td>
                        </tr>

                        <tr class="odd">
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
                        <tr class="even">
                            <td class="col-xs-12">
                                <div class="col-xs-6">
                                    <label for="backup_s3_awsaccesskeyid">AWS Access Key ID:</label>
                                    <input type="text" name="backup_s3_awsaccesskeyid" value="" id="backup_s3_awsaccesskeyid" maxlength="50">
                                </div>
                                <div class="col-xs-6">
                                    <label for="backup_s3_awssecretkey">AWS Secret Key:</label>
                                    <input type="text" name="backup_s3_awssecretkey" value="" id="backup_s3_awssecretkey" maxlength="50">
                                </div>
                            </td>
	                    </tr>
                        <tr class="odd">
                            <td class="col-xs-12">
                                <label for="backup_s3_regionendpoint">AWS Region Endpoint:</label>
                                <select name="backup_s3_regionendpoint" id="backup_s3_regionendpoint">
                                    <option value="us-east-1">us-east-1 (N. Virginia)</option>
                                    <!--<option value="us-east-2">us-east-2 (Ohio)</option>-->
                                    <option value="us-west-1">us-west-1 (N. California)</option>
                                    <option value="us-west-2">us-west-2 (Oregon)</option>
                                </select>
                            </td>
	                    </tr>
                        <tr class="even">
                            <td class="col-xs-12">
                                <label for="backup_s3_bucketname">Bucket Name:</label>
                                <input type="text" name="backup_s3_bucketname" value="" id="backup_s3_bucketname">
                            </td>
	                    </tr>
                        <tr class="odd">
                            <td class="col-xs-12" style="text-align:center;">
                                <input type="submit" value="Save" id="s3-backup-submit">
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
