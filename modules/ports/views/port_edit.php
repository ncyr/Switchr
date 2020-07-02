<div class="content">
    <h2>{{ template:title }}</h2>
    <div class="section">
            {{ if user:logged_in }}
            <div class="panel panel-default create-edit">
                <div class="panel-heading"><h2>My Ports<a id="addPort" style="float: right" class="btn btn-default btn" href="/ports/create"><span class="glyphicon glyphicon-plus"> </span></a></h2> </div>

            {{ streams:form stream="user_port" namespace="ports" mode="edit" edit_id="<?php echo $port_id; ?>" }}

                {{ form_open }}

                <table>

                {{ fields }}

                <tr class="{{ odd_even }}">
                <td width="250">{{ input_title }}{{ required }} <small>{{ instructions }}</small></td>
                <td>{{ error }}{{ input }}</td>
                </tr>

                {{ /fields }}

                </table>

                {{ form_submit }}

                {{ form_close }}

            {{ /streams:form }}

            </div>
           <p>{{ error }}{{ input }}</p>
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
    </div>
</div>
