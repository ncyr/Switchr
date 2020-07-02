<style>
	button, input, optgroup, select, textarea {
	    padding: 5px;
	    width: 99%;
	    font-size: 20px;
	    font-family: 'Raleway', sans-serif;
	}
	.col-sm-8 {
		padding: 60px 35px 0 35px;
	}
</style>
<div id="body" class="section host-create">
    {{ if user:logged_in }}
        <div class="panel panel-default create-edit create-host">
            <div class="panel-heading">
                <h2>{{ template:title }}
                    <a style="float: right" id="backBtn" class="btn btn-default btn" onclick="window.history.go('-1')">
                        <span class="glyphicon glyphicon-arrow-left"></span>
                    </a>
                </h2>
            </div>
			<fieldset>
	            <form method="post" action="">
	                <div class="table-responsive">
		                <table class='table'>
		                    <tr class="{{ odd_even }}">
		                        <td>
							        <label for="host_desc">Host Name:</label>
							  	    <select name="host_id" disabled>
                                            <option id="<?php echo $host_id; ?>" value="<?php echo $host_id; ?>" selected>
                                                <?php echo $host->host_desc; ?>
                                            </option>
                                    </select>
	                            </td>
		                    </tr>
                            <tr class="{{ odd_even }}">
                                <td>
                                    <label for="user-email">User E-mail</label>
                                    <input id="user-email" name="user_email" type="email" value="<?php echo $assigned_user_email?>" disabled>
    							</td>
    						</tr>
                            <tr class="{{ odd_even }}">
                                <td>
                                    <fieldset>
                                        <legend>Choose their permissions:</legend>
                                        <div>
                                            <input type="checkbox" id="ports" name="perm_ports" <?php echo ($current_permissions->perm_ports) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="ports" style="display:inline-block;">Open Ports</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="push" name="perm_push" <?php echo ($current_permissions->perm_push) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="push" style="display:inline-block;">Push Setup to Host</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="info" name="perm_info" <?php echo ($current_permissions->perm_info) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="info" style="display:inline-block;">System Info</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="network" name="perm_network" <?php echo ($current_permissions->perm_network) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="network" style="display:inline-block;">View Network</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="connect" name="perm_restart" <?php echo ($current_permissions->perm_restart) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="connect" style="display:inline-block;">Restart Host</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="remove" name="perm_remove" <?php echo ($current_permissions->perm_remove) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="remove" style="display:inline-block;">Remove Host</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="reset" name="perm_reset" <?php echo ($current_permissions->perm_reset) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="reset" style="display:inline-block;">Reset Connection</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="backup" name="perm_backup" <?php echo ($current_permissions->perm_backup) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="backup" style="display:inline-block;">Off-Site Backup</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="reports" name="perm_reports" <?php echo ($current_permissions->perm_reports) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="reports" style="display:inline-block;">POS Reports</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="fixgrind" name="perm_fixgrind" <?php echo ($current_permissions->perm_fixgrind) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="fixgrind" style="display:inline-block;">Fix WaitingToGrind</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="connect" name="perm_connect" <?php echo ($current_permissions->perm_connect) ? 'checked' : ''; ?> style="width:10%;display:inline-block;">
                                            <label for="connect" style="display:inline-block;">Remote Connect</label>
                                        </div>
                                    </fieldset>
    							</td>
    						</tr>
                        </table>
                    </div>
                    <div class="parent-center" style="width: 100%;">
                        <input type="submit" value="Save" style="margin-bottom:15px; width: 135px;">
                        <input type="button" value="Cancel" onclick="window.history.go('-1');" style="width: 135px;">
                    </div>
			 	</form>
                <p>{{ error }}{{ input }}</p>
            </fieldset>
        </div>
        <p>{{ error }}{{ input }}</p>
    {{ else }}
        <p><a href="users/login">Please login first.</a></p>
    {{ endif }}
</div>
