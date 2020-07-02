<div class="content">
    <div class="section">
            {{ if user:logged_in }}
            <div class="panel panel-default">
                <div class="panel-heading"><h2>{{ template:title }}</h2> </div>
            <table id="messages-table" class="table table-striped ">
                <thead>
                    <tr>
                       <th>Time</th><th>Host</th><th>Message</th><th>Failure</th>
                    </tr>
                </thead>
                <tbody>
                {{ streams:cycle namespace="logging" stream="logging" include="[user_id]" include_by="created_by" paginate="yes" }}
                {{ entries }}
                <tr class="{{ odd_even }}">
                    <td>
                        {{ helper:date format="m/d/Y h:i:s" timestamp=created }}
                    </td>
                    <td>
                        {{ logging_host_id.host_name }}
                    </td>
                    <td>
                        {{ logging_desc }}
                    </td>
                    <td>
                        {{ logging_failure.value }}
                    </td>
                </tr>
                {{ /entries }}
                </tbody>
                <tfoot>
                    
                </tfoot>
            </table>
                {{ error }}{{ input }}
            {{ /streams:cycle }}
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
        </div>
    </div>
</div>
<div id="dialog" style="display: none;" title="">
    <p></p>
</div>