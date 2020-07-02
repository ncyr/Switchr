<div class="content">
    <h2>{{ template:title }}</h2>
    <div class="section">
        <fieldset>
            {{ if user:logged_in }}
            
            <table>
                <tr>
                    <th>Servers Name</th><th>Connection</th>
                </tr>
                {{ streams:cycle namespace="servers" stream="servers" limit="5" include="<?php echo $this->current_user->id;?>" include_by="created_by" }}
                
                <tr class="{{ odd_even }}">
                    <td>{{ server_name }}</td>
                    <td>{{ server_con_status:value }}</td>
                </tr>
                {{ /streams:cycle }}
                {{ pagination }}
            </table>
           <p>{{ error }}{{ input }}</p>
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
        </fieldset>
    </div>
</div>