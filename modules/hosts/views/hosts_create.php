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
	<div id="body" class="section host-create">
          {{ if user:logged_in }}
          <div class="panel panel-default create-host create-edit">
               <div class="panel-heading">
				<h2>{{ template:title }}
					<a style="float: right" id="backBtn" class="btn btn-default btn" onclick="window.history.go('-1')">
	               		<span class="glyphicon glyphicon-arrow-left"></span>
					</a>
				</h2>
			</div>
			<fieldset>
	          {{ streams:form namespace="hosts" stream="hosts" limit="5" mode="new" return="hosts" exclude="host_status_timestamp|host_license|host_ssh_user|host_ssh_pass|host_ssh_port|host_group|host_status|host_info|host_server_id|host_guac_vnc_id|host_guac_rdp_id"}}
	               {{ form_open }}
	               <div class="table-responsive">
	               <table class='table'>
	                    {{ fields }}
					{{ if exists input_slug and input_slug !== "host_guac_vnc_id" and input_slug !== "host_guac_rdp_id" }}
					<td>{{ input_name }}</td>
	                    <tr class="{{ odd_even }}">
	                       	<td>
                              	<label for="host_desc">Host Name:</label>
							{{ input }}
                         	</td>
	                    </tr>
					{{ endif }}

	                    {{ /fields }}
                    <tr class="{{ odd_even }}">
                         <td>
                              <label for="host_server_id">Server:</label>
                              <select id='server' name='host_server_id'>
                                   {{ servers }}
                                   	<option value="{{ id }}">{{ server_name }}</option>
                                   {{ /servers }}
                              </select>
                         </td>
                    </tr>
				</table>
	               {{ form_submit }}
	               {{ form_close }}
	               	<p>{{ error }}{{ input }}</p>
	          {{ /streams:form }}

	        	</fieldset>
     		</div>
          	<p>{{ error }}{{ input }}</p>
          	{{ else }}
          	<p><a href="users/login">Please login first.</a></p>
          	{{ endif }}
        	</fieldset>
        	</div>
	</div>
<script>
	$('#host_desc').attr('placeholder', 'Description of Host');
</script>
