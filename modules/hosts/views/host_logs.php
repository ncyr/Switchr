
<div id="body" class="section">
    {{ if user:logged_in }}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>
                Logs for <?php echo $host->host_desc; ?>
                <a style="float: right" id="backBtn" class="btn btn-default btn" onclick="window.history.go('-1')">
                    <span class="glyphicon glyphicon-arrow-left"></span>
                </a>
            </h2>
        </div>
        <table class="table table-striped datatable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Total Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $index => $log): ?>
                    <tr class="{{ odd_even }}">
                        <td>
                            <?php echo $log->username;  ?>
                        </td>

                        <td>
                            <?php echo $this->hosts_m->day(strtotime($log->start_date)) .' '. $this->hosts_m->hour(strtotime($log->start_date)); ?>
                        </td>

                        <td>
                            <?php echo $this->hosts_m->day(strtotime($log->end_date)) .' '. $this->hosts_m->hour(strtotime($log->end_date)); ?>
                        </td>

                        <td>
                            <!-- Datatables will sort by alpha if there is any alpha. -->
                            <?php echo round(abs(strtotime($log->start_date) - strtotime($log->end_date)) / (60), 0) . " minutes"; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
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

<script>
$(document).ready(function(){
    $('table.datatable').DataTable();
});
</script>
