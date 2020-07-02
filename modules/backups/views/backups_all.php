<div id="body" class="section">
            {{ if user:logged_in }}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2>{{ template:title }}</h2>
                </div>
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th></th>
                        <th>Destination Name</th>
                        <th>Type</th>
                        <!--<th>Limit</th>-->
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                {{ streams:cycle namespace="backups" stream="backup_dest" where="`created_by`='<?php echo $this->current_user->id; ?>'" include="[user_id]" include_by="created_by" paginate="yes" pag_segment="2" exclude="backup_dest_username|backup_dest_password|backup_dest_hostname|backup_dest_port|backup_dest_passive|backup_dest_source|backup_dest_dest|backup_dest_ssh_key|backup_dest_ssh_password|backup_dest_s3_key|backup_dest_s3_msaccess_folders|backup_dest_limit"}}
                {{ entries }}
                <tr class="{{ odd_even }}">
                    <!--<td>
                        <input type="checkbox" class="btn" value="backups[{{ id }}]">
                    </td>-->
                    <td></td>
                    <td>
                        <!--<a href="#" title="Show">{{ backup_dest_name }}</a>-->
                        <a href="/backups/edit/{{ backup_dest_type }}/{{ id }}" title="Edit">{{ backup_dest_name }}</a>
                    </td>

                    <td>
                        {{ backup_dest_type }}
                    </td>
                    <!-- <td>
                        {{ backup_dest_limit }} MB
                    </td> -->
                    <td><a class="confirm btn btn-danger delete-backup" href="/backups/delete/{{ id }}">Remove</a>&nbsp;</td>
                {{ /entries }}
                <!--<div>{{ pagination }}</div>-->
                {{ /streams:cycle }}
                </tbody>
                <tfoot></tfoot>
            </table>
            {{ error }}{{ input }}
            {{ if error }}
            <div class="alert alert-danger" role="alert">
                <strong>Oh snap!</strong> There was a problem with something: {{ input }}
            </div>

            {{ endif }}
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
        </div>
    </div>

</div>
<div id="addHostForm" style="display: none;" title="Create A Host">
    <p>
        <!--<form class="">
            <div class="form-group">
                <label for="host_name">Host Name</label>
                <input name="host_name" type="text" class="form-control" placeholder="Description of Host">
                <label for="server_location">Server Location</label>
                <select name="server_location" class="form-control" placeholder="Host Name">
                    <option value="">testsdfadsfasdfsdafsadfsdaf</option>
                </select>
            </div>
                <button type="submit" class="btn btn-default">Submit</button>
        </form>-->
        <fieldset>
            {{ if user:logged_in }}
            {{ streams:form namespace="hosts" stream="hosts" limit="5" mode="new" creator_only="yes" return="hosts" exclude="host_license|host_ssh_user|host_ssh_pass|host_ssh_port|host_group"}}
                {{ form_open }}
                <div class="form-group">
                    {{ fields }}
                    {{ input_title }}
                         {{ input }}
                    {{ /fields }}
                    </div>
                {{ form_submit }}
                {{ form_close }}
            {{ /streams:form }}
           <p>{{ error }}{{ input }}</p>
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
        </fieldset>
    </p>
<div id="dialog" style="display: none;" title="">
    <p></p>
</div>
