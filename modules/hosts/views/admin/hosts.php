<div class="content">
    <h2>{{ template:title }}</h2>
    <div class="section">
            {{ if user:logged_in }}
            <div class="table-responsive">
            <table class="table table-striped">
                <tr>
                    <th>Your Hosts</th><th>Connection</th>
                </tr>
                {{ streams:cycle namespace="hosts" stream="hosts" limit="5" include="<?php echo $this->current_user->id;?>" include_by="created_by" }}
                
                <tr class="{{ odd_even }}">
                    <td>{{ host_desc }}</td>
                    <td>{{ host_server_id:value }}</td>
                </tr>
                {{ /streams:cycle }}
                {{ pagination }}
            </table>
			</div>
           <p>{{ error }}{{ input }}</p>
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
    </div>
</div>
