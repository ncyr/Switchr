<div id="body" class="section">
    {{ if user:logged_in }}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{{ template:title }}
                <a style="float: right" id="editPlan" class="btn btn-info" href="/payignite/edit/{{ subscription.id }}">
                    Edit Plan
                </a>
            </h2>
        </div>
        <table class="table table-striped datatable">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Total Amount</th>
                </tr>
            </thead>

            <tbody>
            <tr>
                <td>Hosts</td>
                <td>{{ plan_parsed.hosts }}</td>
            </tr>
            <!-- <tr>
                <td>S3 Backups</td>
                <td>{{ plan_parsed.s3 }}</td>
            </tr>
            <tr>
                <td>FTP Backups</td>
                <td>{{ plan_parsed.ftp }}</td>
            </tr>
            <tr>
                <td>Local Backups</td>
                <td>{{ plan_parsed.local }}</td>
            </tr> -->
            </tbody>

            <tfoot></tfoot>
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
