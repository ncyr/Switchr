<div id="body" class="section">
        {{ if user:logged_in }}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>{{ template:title }}
                <a id="backBtn" class="btn btn-default btn" onclick="window.history.go('-1')">
                    <span class="glyphicon glyphicon-arrow-left"></span> Back</a>
                <a id="addPort" class="btn btn-default btn" href="/ports/create/<?php echo $host_id;?> ">
            		<span class="glyphicon glyphicon-plus"></span> Add Port</a>
            	</h2>
            </div>
        <table class="table table-striped datatable">
            <thead>
            <tr>
                    <th><a class="help" title="Port Status Switch" description="Turn this on, to open acccess to your host by your defined rule. Turn it off to close public access, and secure your host.">Status</a>
                    </th><th><a class="help" title="Service Name" description="This will be the descriptive name you give the port. It can be something like, VNC, RDP, HTTP, etc, or whatever you name it.">Service Name</a>
                    </th><th><a class="help" title="Local Port" description="This is the port you want to serve content from on your host machine. IE, default web is port 80, and 443 for secured content.
                        <br/>See also: <a href='https://msdn.microsoft.com/en-us/library/cc959833.aspx' target='_blank'>Port Assignments for Common Services</a>">Private Port</a>
                    </th><th><a class="help" title="Public Port" description="This is the remote ip address that has been assigned to your port on the web for access through secured networks.">Public Port</a>
                    </th><th><a class="help" title="Protocol" description="Defines your servers transport method, typically TCP, or Both. <br />See also: <a href='https://msdn.microsoft.com/en-us/library/cc959833.aspx' target='_blank'>Port Assignments for Common Services</a>">Protocol</a>
                    </th><th><a class="help" title="IP Rule" description="Secure your open port to a specified computer address, or subnet on the web, to have access to the public port. <br />Accept Anything: 0.0.0.0/0<br />Accept Subnet: x.x.x.0/24 ">IP Rule</a>
                    </th><th>
                    </th>
            </tr>
            </thead>
            <?php if ($host_id):?>
            {{ streams:cycle namespace="ports" stream="user_port" limit="5" include="<?php echo $host_id;?>" include_by="host_id" }}
            <?php else: ?>
            {{ streams:cycle namespace="ports" stream="user_port" limit="5" include="<?php echo $this->current_user->id;?>" include_by="created_by" }}
            <?php endif; ?>
            <tbody>
            <tr class="{{ odd_even }}">
                <td>
                    {{ if is_active:key == 1 }}
                    <a id="{{ id }}" class="switch confirm active" title="Enabled"><button class="btn btn-success">ON</button></a>
                    {{ else }}
                    <a id="{{ id }}" class="switch confirm" title="Disabled"><button class="btn btn-danger">OFF</button></a>
                    {{ endif }}
                </td>
                <td>{{ service_name }}</td>
                <td>{{ local_port }}</td>
                <td>{{ remote_port }}</td>
                <td>{{ protocol:value }}</td>
                <td>{{ ip_rule }}</td>
                <td><a id="{{ id }}" class="confirm-delete btn btn-danger">Remove</a></td>
            </tr>
            </tbody>
            {{ /streams:cycle }}
            {{ pagination }}
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

<!-- DIALOGS & POP FORMS ---->
<div id="addPortForm" class="dialog" style="display: none;" title="Forward a Port">
    <div id="body" class="section host-create">
        <fieldset>
            {{ if user:logged_in }}
            {{ streams:form namespace="ports" stream="user_port" mode="new" creator_only="yes" return="hosts" exclude="host_id|server_id|mac_rule|remote_port" }}
                {{ form_open }}
                <div class="form-group">
                    {{ fields }}
                    <label for="{{ input_slug }}">{{ input_title }}</label>
                         {{ input }}
                    {{ /fields }}
                    <input type='hidden' name='host_id' value='<?php echo $host_id ?>'>
                    <input type='hidden' id="server" name='server_id' value='<?php echo $server_id ?>'>
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
    </div>
</div>
<div id="dialog" style="display: none;" title="">
    <p></p>
</div>
