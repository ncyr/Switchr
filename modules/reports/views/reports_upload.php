
    <div id="body" class="section">
            {{ if user:logged_in }}
            <div class="panel panel-default">
                <div class="panel-heading"><h2>{{ template:title }}</h2> </div>
          	<form method="post" action="/reports/putLocalStatement/<? echo $this->uri->segment(3);?>" enctype="multipart/form-data">
          	<label for="statementUpload">Upload A Statement</label>
          	<input type="text" class="date-picker" name="date">
          	<input type="file" name="file">
          	<input type="submit" name="submitBtn" value="Upload">
	       	</form>
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