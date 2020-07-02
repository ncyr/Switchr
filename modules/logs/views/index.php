<div id="body" class="section">
    {{ if user:logged_in }}
        <div class="panel panel-default">
            <div class="panel-heading"><h2>Logs</h2>
            </div>
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>Created</th><th>Host</th><th>Message</th><th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{ streams:cycle namespace="logging" stream="logging" include="<?php echo $this->current_user->id;?>" include_by="created_by" }}

                        <tr class="{{ odd_even }}">
                            <td>{{ helper:date format="m/d/Y h:i:s" timestamp=created }}</td>
                            <td>{{ logging_host_id:host_desc }}</td>
                            <td>{{ logging_desc }}</td>
                            <td>{{ logging_ip }}</td>
                        </tr>

                        {{ /streams:cycle }}
                        {{ pagination }}
                    </tbody>
                </table>
    	</div>
        <p>{{ error }}{{ input }}</p>
    {{ else }}
        <p><a href="users/login">Please login first.</a></p>
    {{ endif }}
</div>
<script>
// Enable DataTables
jQuery(document).ready(function($){
    $('table').DataTable({
    });
});
</script>
