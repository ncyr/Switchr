<div id="body" class="section">
    {{ if user:logged_in }}


    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{{ template:title }}</h2>
        </div>
        {{ if message }}
            <h3>{{ message }}</h3>
        {{ endif }}
        {{ streams:form stream="subscriptions" namespace="payignite" mode="edit" edit_id=<?php echo $data['subscription']['id']; ?> return="/payignite" }}
        {{ form_open }}
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
                <td><input id="hosts" type="number" size="5" name="hosts" class="amount" value="{{ plan_parsed.hosts }}" min=0 required style="width:initial;"></td>
            </tr>
            <!-- <tr>
                <td>S3 Backups</td>
                <td><input id="s3" type="number" size="5" name="s3" class="amount" value="{{ plan_parsed.s3 }}" min=0 required style="width:initial;"></td>
            </tr>
            <tr>
                <td>FTP Backups</td>
                <td><input id="ftp" type="number" size="5" name="ftp" class="amount" value="{{ plan_parsed.ftp }}" min=0 required style="width:initial;"></td>
            </tr>
            <tr>
                <td>Local Backups</td>
                <td><input id="local" type="number" size="5" name="local" class="amount" value="{{ plan_parsed.local }}" min=0 required style="width:initial;"></td>
            </tr> -->
            </tbody>

        </table>

        <input class="btn btn-info" type="submit" value="Save">
        <a id="cancel-plan" class="btn btn-danger" style="float: right; position:relative;">
           Cancel Subscription
        </a>

        {{ form_close }}
        {{ /streams:form }}
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

    <!-- Modal for trying to pay for less resources than are currently used. -->
    <div id="payignite-edit" class="modal fade payignite-edit" tabindex="-1" role="dialog" aria-labelledby="payigniteEdit">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Whoa there partner!</h4>
            </div>
            <div class="modal-body">
                <p><strong>You need to delete some <a href="/hosts">Hosts</a> or <a href="/backups">Backups</a> before you reduce your plan.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
      </div>
    </div>

    <!-- Modal for cancelling subscription.-->
    <div id="payignite-cancel" class="modal fade payignite-cancel" tabindex="-1" role="dialog" aria-labelledby="payigniteCancel">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Whoa there partner!</h4>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to cancel your super-awesome subscription to the coolest service ever?</strong></p>
            </div>
            <div class="modal-footer">
                <a id="delete" class="btn btn-warning" type="button" href="/payignite/delete/{{ subscription.id }}">Delete it!</a>
                <button class="btn" type="button" data-dismiss="modal">Cancel</button>
            </div>
        </div>
      </div>
    </div>

<script>
$(document).ready(function() {
    $('input[type="submit"]').on("click", function(e){
        if ($( "#hosts" ).val() < {{ hosts_used }}) {
        // || $( "#s3" ).val() < {{ backups_used.s3 }}
        // || $( "#ftp" ).val() < {{ backups_used.ftp }}
        // || $( "#local" ).val() < {{ backups_used.local }})
            $('#payignite-edit').modal('show');
            //e.preventDefault();
            return false;
        }
    });

    $('#cancel-plan').on("click", function(e){
        $('#payignite-cancel').modal('show');
        return false;
    });
});
</script>

</div>
