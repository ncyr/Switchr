
    <div id="body" class="section">
            {{ if user:logged_in }}
            <div class="panel panel-default">
                <div class="panel-heading"><h2>{{ template:title }}</h2> </div>
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                           <th>Opened</th><th>Closed</th><th>Port</th><th>Input Usage (bytes)</th><th>Output Usage (bytes)</th>
                    </tr>
                </thead>
                <tbody>
                {{ streams:cycle namespace="hosts" stream="host_band" include="<?php echo $host_id;?>" include_by="host_band_host_id" paginate="yes" pag_segment="2"}}
                {{ entries }}
                <tr class="{{ odd_even }}">
                	<td>
                        {{ helper:date format="Y-m-d h:i:s" timestamp=created }}
                    </td>
                    <td>
                    	{{ if updated }}
                        {{ helper:date format="Y-m-d h:i:s" timestamp=updated }}
                        {{ endif }}
                    </td>
                    <td>
                        {{ host_band_port }}
                    </td>
                    <td>
                        {{ host_band_input }}
                    </td>
                    <td>
                        {{ host_band_output }}
                    </td>
                {{ /entries }}
                <!--<div>{{ pagination }}</div>-->
                {{ /streams:cycle }}
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
