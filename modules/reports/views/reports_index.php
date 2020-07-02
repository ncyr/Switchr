
<div id="body" class="section">
    {{ if user:logged_in }}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>{{ template:title }}
                	<a style="float:right" id="uploadBtn" class="btn btn-default" onclick="window.history.go('-1')"><span class="glyphicon glyphicon-arrow-left"></span> Back</a>&nbsp;&nbsp;
                    <!--<a style="float:right" id="uploadBtn" class="btn btn-default" href="/reports/resetPOS/<?php //echo $this->uri->segment(3);?>"><span class="glyphicon glyphicon-retweet"></span> Refresh Data</a>-->
                		<!--<a style="float:right" id="uploadBtn" class="btn btn-default" href="/reports/upload/<?php //echo $this->uri->segment(3);?>"><span class="glyphicon glyphicon-upload"></span>Upload</a>
                		<a style="float:right" id="statementsBtn" class="btn btn-default" href="/reports/statements/"><span class="glyphicon glyphicon-calendar"></span>Statements</a>-->
                	<div class="cboth"></div>
                </h2>
            </div>
            	<section class="item">
				<div class="subheader">
					<div id="reportTypeWrapper" class="floatLeft">
						<?php
                            if (isset($reportSettings)) {
                                echo '<label for="report_type">Report Name:</label><br>';
                                // Sort the available reports by type (sls (sales), lbr (labor)).
                                // $dropSetting[type][index][report name]
                                // ex: $dropSetting["sls"][0]["Default.sls.exp"]
                                $dropSetting = array();
                                foreach ($reportSettings as $key => $value) {
                                    $last = strrpos($value, ".");
                                    $nextLast = strrpos($value, ".", $last - strlen($value) - 1);
                                    $ext = substr($value, ($nextLast+1), -4);
                                    $dropSetting[$ext][] = $value;
                                }
                                $sorted_reportSettings = array();
                                foreach ($dropSetting as $ext => $array) {
                                    foreach ($array as $index => $name) {
                                        $sorted_reportSettings[$name] = $name;
                                    }
                                }
                                echo form_dropdown('reportSetting', $sorted_reportSettings);
                            }
                        ?>
					</div>

					<div id="date_range" class="floatLeft">
						<?php
                            if (isset($reportDates)) {
                                $sorted_reportDates = array();
                                $sorted_reportDates['Today'] = 'Today';  // Add 'Today' as an option. The rest are dated directories.
                                foreach ($reportDates as $index => $value) {
                                    $sorted_reportDates[$value] = $value;
                                }
                                echo '<label for="report_type">Start Date:</label><br>';
                                echo form_dropdown('startDate', $sorted_reportDates);
                                echo '<br>';
                                echo '<label for="report_type">End Date:</label><br>';
                                echo form_dropdown('endDate', $sorted_reportDates);
                            }
                        ?>
					</div>

					<div class="floatLeft" style="margin: 21px 0 0 21px">
						<label for="sendTo">Send To:</label>
						<select id="sendTo" name="sendTo">
							<option value="screen">Screen</option>
							<option value="email">E-mail</option>
							<option value="sms">SMS</option>
						</select>
						<br>
						<div id="smsphone" style="display:none;">
                            <label for="sendToNum">Enter US Phone Number (no dash or special chars)</label>
                            <input id="sendToNum" type="text" name="sendToNum">
                        </div>
					</div>
					<div class="floatLeft">
						<input id="host_id" name="host_id" type="hidden" value="<?php echo $this->uri->segment(3); ?>">
						<input id="mod" name="mod" type="hidden" value="<?php echo $this->uri->segment(4); ?>">
						<button class="floatRight" type="submit" name="rptSubmitBtn"/>Show</button>
				        <!--<a class="floatLeft submenu" href="#"><span class="glyphicon glyphicon-cog"></span> Settings</a>-->
					</div>
					<div class="cleft"></div>
				</div>
				<hr/>
				<div id="reportContent">
				</div>
			    </section><br />
			    <?php if (!$sorted_reportSettings) {
                            echo '<div class="alert alert-danger" style="text-align: center" role="alert">
					    <strong>No Host Connection</strong>
                        <br>Unable to obtain existing report settings! Please make sure the host is connected, you may also be logged out, or the session expired.
					    <br>
					    <button>Refresh</button>
					    </div>';
                        }?>
                {{ error }}{{ input }}
                {{ if error }}
                    <div class="alert alert-danger" style="text-align: center" role="alert">
                        <strong>Oh snap!</strong> There was a problem with something: {{ input }}
                    </div>

                {{ endif }}
    {{ else }}
        <p><a href="users/login">Please login first.</a></p>
    {{ endif }}
    </div>
</div>

</div> <!-- extra ending tag? -->
<div id="dialog" style="display: none;" title="">
    <p></p>
</div>
