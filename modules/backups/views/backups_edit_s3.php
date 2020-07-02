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
                <form id="edit-s3-form" action="/backups/edit/awss3/{{ backup_id }}" method="post" enctype="multipart/form-data">
	                <div class="table-responsive">
	                <table class='table'>
                        <input type="hidden" name="backup_dest_host_id" id="backup_dest_host_id" value="{{ host_id }}">
                        <input type="hidden" name="backup_dest_type" id="backup_dest_type" value="awss3">
                        <input type="hidden" name="backup_s3_awsaccesskeyid" value="{{ backup_s3_awsaccesskeyid }}" id="backup_s3_awsaccesskeyid" maxlength="50">
                        <input type="hidden" name="backup_s3_awssecretkey" value="{{ backup_s3_awssecretkey }}" id="backup_s3_awssecretkey" maxlength="50">
                        <tr class="odd">
                            <td class="col-xs-12">
                                <label for="backup_dest_name">Display Name:</label>
                                <input name="backup_dest_name" id="backup_dest_name" type="text" maxlength="50" value="{{ backup_dest_name }}">
                            </td>
                        </tr>
                        <tr class="even">
                            <td class="col-xs-12">
                                <label for="backup_dest_uploadat">When to upload:</label>
                                <input type="text" name="backup_dest_uploadat" id="cronString" value="{{ backup_dest_uploadat }}" readonly="readonly" style="background-color:rgb(235, 235, 228);">
                                <div class="upload-at"></div>
                            </td>
                        </tr>
                        <tr class="odd">
	                        <td class="col-xs-12">
                                <div class="col-xs-6">
                                    <label for="backup_s3_bucketname">Bucket Name:</label>
                                    <input type="text" name="backup_s3_bucketname" value="{{ backup_s3_bucketname }}" id="backup_s3_bucketname" readonly="readonly" style="background-color:rgb(235, 235, 228);">
                                </div>
                                <div class="col-xs-6">
                                    <label for="backup_s3_regionendpoint">AWS Region Endpoint:</label>
                                    <input type="text" name="backup_s3_regionendpoint" value="{{ backup_s3_regionendpoint }}" id="backup_s3_regionendpoint" maxlength="50" readonly="readonly" style="background-color:rgb(235, 235, 228);">
                                </div>
                            </td>
	                    </tr>
                        <tr class="even">
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
                        <tr class="odd">
                            <td style="text-align:center;">
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
        window.edit = true;
        // turn the div into a cron editor
        $('.upload-at').croneditor({
            value: "{{ backup_dest_uploadat }}"  // This will load the correct value, but the cron GUI doesn't. Fixable?
        });
        // Load the current dropdown option to the user, otherwise it will default to the first option.
        // $('#backup_dest_status').val('{{ backup_dest_status }}')

        // Parse selected items to the source client view.
        // $('#backup_dest_source').val('{{ backup_dest_source }}')
        var selected_items = '{{ backup_dest_source }}';
        selected_items = selected_items.replace(/\\/g, "\\\\");
        selected_items = JSON.parse(selected_items)

        // Parse the source-selected-list from database and insert into the SSH client on this edit page.
        $(selected_items).each(function(key, path) {
            // Recursive directory.
            if (path.slice(-4) == '\\.\\*') {
                // Convert path to base64.
                var path64 = btoa(path.slice(0, -4));
                // Strip slash and star.
                path = path.slice(0, -4);
                window.selected_dir_list[path64] = new Object();
                window.selected_dir_list[path64].recursive = true;
                $('#source-selected-list').append(
                    // This wrapper div is needed to remove the item from the list using selectedRemove().
                    "<div data-id='"+path64+"' data-type='dir' class='source-selected source-selected-parent' style='width:100%;'>"+
                        "<div class='source-selected' style='width:50%;display:inline-block;'>"+
                            "<li class='source-selected source-selected-dir' style='width:100%;' data-value='"+path64+"'>"+path+"</li>"+
                        "</div>"+
                        // Checkbox for recursive.
                        "<input data-id='"+path64+"' type='checkbox' value='"+path64+"' class='source-selected source-selected-recursive' style='width:15px;height:15px;display:inline-block;margin-left:20px;margin-right:10px;' onclick='sourceSelectedClickRecursive(this);'>"+

                        "<button class='source-selected source-selected-remove' style='width:75px;display:inline-block;margin-left:20px;' onclick='selectedRemove(this);'>Remove</button>"+
                    "</div>");
                $('.source-selected-recursive[data-id="'+path64+'"]').prop('checked', true);
            // Non-recursive directory.
            } else if (path.slice(-2) == '\\*') {
                // Convert path to base64.
                var path64 = btoa(path.slice(0, -2));
                // Strip slash and star.
                path = path.slice(0, -2);
                window.selected_dir_list[path64] = new Object();
                window.selected_dir_list[path64].recursive = false;

                $('#source-selected-list').append(
                    // This wrapper div is needed to remove the item from the list using selectedRemove().
                    "<div data-id='"+path64+"' data-type='dir' class='source-selected source-selected-parent' style='width:100%;'>"+
                        "<div class='source-selected' style='width:50%;display:inline-block;'>"+
                            "<li class='source-selected source-selected-dir' style='width:100%;' data-value='"+path64+"'>"+path+"</li>"+
                        "</div>"+
                        // Checkbox for recursive.
                        "<input data-id='"+path64+"' type='checkbox' value='"+path64+"' class='source-selected source-selected-recursive' style='width:15px;height:15px;display:inline-block;margin-left:20px;margin-right:10px;' onclick='sourceSelectedClickRecursive(this);'>"+

                        "<button class='source-selected source-selected-remove' style='width:75px;display:inline-block;margin-left:20px;' onclick='selectedRemove(this);'>Remove</button>"+
                    "</div>");
                $('.source-selected-recursive[data-id="'+path64+'"]').prop('checked', false);
            // File.
            } else {
                // Convert path to base64.
                var path64 = btoa(path);
                window.selected_file_list[path64] = new Object();
                $('#source-selected-list').append(
                    // This wrapper div is needed to remove the item from the list using selectedRemove().
                    "<div data-id='"+path64+"' data-type='file' class='source-selected source-selected-parent' style='width:100%;'>"+
                        "<div class='source-selected' style='width:50%;display:inline-block;'>"+
                            // Grab with $(this).data('value')
                            "<li class='source-selected source-selected-dir source-selected-file' style='width:100%;' data-value='"+path64+"'>"+path+"</li>"+
                        "</div>"+

                        "<div style='width:15px;height:15px;display:inline-block;margin-left:20px;margin-right:10px;'></div>"+

                        "<button class='source-selected source-selected-remove' style='width:75px;display:inline-block;margin-left:20px;' onclick='selectedRemove(this);'>Remove</button>"+
                    "</div>");
            }
        });
    });
</script>
