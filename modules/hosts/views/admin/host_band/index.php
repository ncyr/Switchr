<div class="content">
    <h2>{{ template:title }}</h2>
    <div class="section">
            {{ if user:logged_in }}
            <div class="table-responsive">
            <table class="table table-striped">
                <tr>
                    <th>Port</th><th>Input Usage</th><th>Output Usage</th>
                </tr>
                {{ streams:cycle namespace="hosts" stream="host_band" limit="5" include="<?php echo $this->current_user->id;?>" include_by="created_by" }}
                
                <tr class="{{ odd_even }}">
                    <td>{{ host_band_port }}</td>
                    <td>{{ host_band_input }}</td>
                    <td>{{ host_band_output }}</td>
                </tr>
                {{ /streams:cycle }}
                {{ pagination }}
            </table>
			</div>
           <p>{{ error }}{{ input }}</p>
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
    </div>
</div>