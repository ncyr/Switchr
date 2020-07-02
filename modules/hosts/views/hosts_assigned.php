<!-- <div class="content"> -->
<div id="body" class="section">
    {{ if user:logged_in }}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>{{ template:title }}
                    <a style="float: right" id="backBtn" class="btn btn-default btn" onclick="window.history.go('-1')">
                        <span class="glyphicon glyphicon-arrow-left"></span>
                    </a>
                </h2>
            </div>

            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Host Name</th>
                        <th>User</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($host_id): ?>
                        {{ streams:cycle namespace="hosts" stream="host_users" include="<?php echo $this->current_user->id;?>" include_by="created_by" include="<?php echo $host_id;?>" include_by="host_id" }}
                    <?php else: ?>
                        {{ streams:cycle namespace="hosts" stream="host_users" include="<?php echo $this->current_user->id;?>" include_by="created_by" }}
                    <?php endif; ?>
                    {{ entries }}
                        <tr class="{{ odd_even }}">
                            <td style="vertical-align:middle;">{{ host_id.host_desc }}</td>
                            <td style="vertical-align:middle;">{{ user_id.email }}</td>
                            <td>
                                <a href="/hosts/assign_user_edit/{{ host_id.id }}/{{ user_id.user_id }}" class="confirm" title="Edit User Permissions">
                                    <button class="btn btn-info">Edit</button>
                                </a>
                            </td>
                            <td>
                                <a href="/hosts/delete_assigned/{{ host_id.id }}/{{ user_id.user_id }}" class="confirm" title="Remove Assigned User">
                                    <button class="btn btn-danger">Remove</button>
                                </a>
                            </td>
                        </tr>
                    {{ /entries }}
                    {{ /streams:cycle }}
                    {{ pagination }}
                </tbody>
            </table>
                <p>{{ error }}{{ input }}</p>
            {{ else }}
                <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
        </div>
</div>
