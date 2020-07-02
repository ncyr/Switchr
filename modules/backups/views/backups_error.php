<div id="body" class="section">
    {{ if user:logged_in }}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>{{ template:title }}
                    <a style="float: right" id="addHost" class="btn btn-default btn" href="/backups/create/awss3/<?php echo $host_id;?>"><span class="glyphicon glyphicon-plus">Add S3</span></a>
                    <a style="float: right" id="addHost" class="btn btn-default btn" href="/backups/create/ftp/<?php echo $host_id;?>"><span class="glyphicon glyphicon-plus">Add FTP/SFTP</span></a>
                    <a style="float: right" id="addHost" class="btn btn-default btn" href="/backups/create/local/<?php echo $host_id;?>"><span class="glyphicon glyphicon-plus">Add Local</span></a>

                </h2>
            </div>

            {{ if message }}
            <div class="alert alert-danger" role="alert">
                <p><strong>{{ message }}</strong></p>
            </div>
            {{ endif }}

            {{ error }}{{ input }}
            {{ if error }}
                <div class="alert alert-danger" role="alert">
                    <strong>Oh snap!</strong> There was a problem with something: {{ input }}
                </div>
            {{ endif }}
        </div>
    {{ else }}
            <p><a href="users/login">Please login first.</a></p>
    {{ endif }}
</div>

</div>

<div id="dialog" style="display: none;" title="">
    <p></p>
</div>
