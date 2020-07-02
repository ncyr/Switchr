
            {{ if user:logged_in }}

				 <fieldset>
	            {{ streams:form namespace="hosts" stream="hosts" limit="5" mode="new" creator_only="yes" return="hosts" exclude="host_status_timestamp|host_license|host_ssh_user|host_ssh_pass|host_ssh_port|host_group|host_status|host_info"}}
	                {{ form_open }}
	                <div class="table-responsive">
	                <table class='table'>
	                    {{ fields }}
					{{ if exists input_slug and input_slug !== "host_guac_id" }}
					<td>{{ input_name }}</td>
	                    <tr class="{{ odd_even }}">
	                        <td>{{ input }}</td>
	                    </tr>
					{{ endif }}
	                    {{ /fields }}
				</table>
	                {{ form_submit }}
	                {{ form_close }}
	                <p>{{ error }}{{ input }}</p>
	            {{ /streams:form }}

	        	</fieldset>

			<!--<div class="col-sm-4" style="background-color: #BAB9B9;height: 1500px;border: 5px solid #929292;x">

				<h2>Host Additions:</h2>
				<p>Create a host within your account to control and backup, and to share with other users you provide access to.
					<br><br>
					If you have questions about adding hosts , Please submit a ticket to <a href="https://switchr.io/support">Our Support Team</a>
				</p>
				<p><b>Host Description- </b></p>
				<p>The name of which you'd like to be able to search and filter.</p>
				<p><b>Server Name - </b></p>
				<p>The server your creating this hosts user on.</p>
				<p><b>Status- </b></p>
				<p>This will be the default state of the host. Active or Disabled.</p>-->

           <p>{{ error }}{{ input }}</p>
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
        	</fieldset>
