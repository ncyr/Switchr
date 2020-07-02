
<div id="body" class="section">
{{ if user:logged_in }}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{{ template:title }}
                <a style="float:right" id="uploadBtn" class="btn btn-default" onclick="window.history.go('-1')"><span class="glyphicon glyphicon-arrow-left"></span> Back</a>&nbsp;&nbsp;
                <div class="cboth"></div>
            </h2>
        </div>
            <section class="item">
            <div class="subheader">
                <div id="reportTypeWrapper" class="floatLeft">
                    <?php
                        echo form_dropdown('filename', $filename);
                    ?>
                </div>
                <div class="floatLeft">
                    <input id="host_id" name="host_id" type="hidden" value="<?php echo $this->uri->segment(3); ?>">
                    <input id="mod" name="mod" type="hidden" value="<?php echo $this->uri->segment(4); ?>">
                    <button class="floatRight" type="submit" name="dbgSubmitBtn"/>Show</button>
                    <!--<a class="floatLeft submenu" href="#"><span class="glyphicon glyphicon-cog"></span> Settings</a>-->
                </div>
                <div class="cleft"></div>
            </div>
            <hr/>
            <div id="reportContent">
            </div>
            </section><br />
            <?php if (!$filename) {
                        echo '<div class="alert alert-danger" style="text-align: center" role="alert">
                    <strong>No Host Connection</strong>
                    <br>Unable to obtain existing file list from the host, please make sure it is connected.
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
