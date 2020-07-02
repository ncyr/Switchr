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
    <div id="body" class="section port-create">
            {{ if user:logged_in }}
            <div class="panel panel-default create-edit">
                <div class="panel-heading"><h2>{{ template:title }}<a style="float: right" id="backBtn" class="btn btn-default btn" onclick="window.history.go('-1')">
                			<span class="glyphicon glyphicon-arrow-left">Back</span></a></h2></div>
				 <fieldset>
					 <center>
            {{ if user:logged_in }}
            {{ streams:form namespace="ports" stream="user_port" limit="1" mode="new" creator_only="yes" return="ports" exclude="remote_port|host_id|server_id|mac_rule" }}
                {{ form_open }}
                <table>
                    {{ fields }}
                    <td> {{ input_title }}</td>
                    <tr class="{{ odd_even }}">
                        <td> {{ input }}</td>
                    </tr>
                    {{ /fields }}
                    <tr>
                        <td>
                        <input type='hidden' name='host_id' value='<?php echo $host_id ?>'>
                        <input type='hidden' id="server" name='server_id' value='<?php echo $server_id ?>'>
                        </td>
                    </tr>

                </table><br />
                {{ form_submit }}
                {{ form_close }}
            {{ /streams:form }}

            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
	  	</center>
        </fieldset>
        </div>
           <p>{{ error }}{{ input }}</p>
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
        	</fieldset>
        	</div>
    </div>
