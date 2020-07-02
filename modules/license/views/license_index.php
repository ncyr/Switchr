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
                <h2>{{ template:title }}
                    <a style="float: right" class="btn btn-default btn" onclick="window.history.go('-1')">
            	        <span class="glyphicon glyphicon-arrow-left">Back</span>
                    </a>
                </h2>
            </div>
    		<fieldset>
                <center>
    			<p>
        			<br />The licensing section is made to offer easy end-user installation without requiring the main account holder username, or password.
                    <br /><br />Ask the end user to download the application from our website, and provide them the serial key for install. Once the user enters the key and proceeds,
        		    the application should connect, and notify the user in the task bar that it is connected. Once a connection is made, you have access to the machine from this web console.
                    <br />
                    <br /><a class="help" title="Host Licensing" description="For security purposes, once a license has been installed or expired,
                        it cannot be installed again without a renewal. Please contact support for help resolving this matter.">Need Help?</a>
                </p>
                {{ if user:logged_in }}
                    <?php if (isset($host_id)):?>
                        <!-- host_license (and here, license_id) is NULL if using streams->get_entry() on host. Cause unkown. -->
                        {{ streams:cycle namespace="license" stream="license_serials" limit="1" include="<?php echo $license_id?>" include_by="id" }}
                        {{ entries }}
                    <?php else:?>
                        {{ streams:cycle namespace="license" stream="license_serials" limit="5" include="[user_id]" include_by="created_by" }}
                        {{ entries }}
                    <?php endif; ?>
                        <p>
                            <b>License Serial: </b>
                            <span id="license-serial">{{ license_serial }}</span>
                            <br>
                            <b>Installation Status: </b>{{ license_status:value }}
                        </p>
                        {{ /entries }}
                        {{ /streams:cycle }}

                        <p><a href="<?php echo BASE_URL.'files/Install_Remote.exe';?>">Installation File (Windows XP,7,10)</a></p>
                        <p><a href="#" id="email-license">Email this license</a></p>
                        <button class="btn" id="copy-license" data-clipboard-target="#license-serial">
                            Copy Serial to clipboard
                        </button>
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

<form id="form-email-license" class="" style="display:none">
    <label for="email-address">Email Address: </label>
    <input type="email" name="email-address" id="email-address">
</form>

<script>
    // Need this to initialize the js clipboard library.
    var clipboard = new Clipboard('#copy-license');

    // Change text of button on success or error.
    clipboard.on('success', function(e) {
        $('#copy-license').text('Copied!');
    });
    clipboard.on('error', function(e) {
        $('#copy-license').text('Press Ctrl+C to copy.');
    });


</script>
