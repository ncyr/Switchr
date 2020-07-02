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
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2>{{ template:title }}</h2>
                </div>
                    {{ streams:form namespace="backups" stream="backup_dest" mode="new" return="backups" exclude="backup_dest_username|backup_dest_password|backup_dest_hostname|backup_dest_dest|backup_dest_port|backup_dest_passive|backup_dest_ssh_key|backup_dest_ssh_password|backup_dest_status|backup_dest_type|backup_dest_host_id|backup_s3_desc|backup_s3_bucketname|backup_s3_awsaccesskeyid|backup_s3_awssecretkey|backup_s3_serviceurl|backup_s3_regionendpoint|backup_s3_file1|backup_s3_msaccess_folders"}}
                    {{ form_open }}
                    <div class="table-responsive">
                    <table class='table'>
                        <input type="hidden" name="backup_dest_host_id" value=<?php echo $host_id; ?>>
                        {{ fields }}
                        <td> {{ input_title }}</td>
                        <tr class="{{ odd_even }}">
                            <td> {{ input }}</td>
                        </tr>
                        {{ /fields }}
                        <input type="hidden" name="backup_dest_type" value="<?php echo $type;?>">
                    </table>
                    {{ form_submit }}
                    {{ form_close }}
                    <p>{{ error }}{{ input }}</p>
                {{ /streams:form }}
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
