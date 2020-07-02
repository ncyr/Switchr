<div class="content">
    <h2>{{ template:title }}</h2>
    <div class="section">
        <fieldset>
            {{ if user:logged_in }}
            <a class="btn blue" href="hosts/local_port_used/create">Add Port</a>
            <table>
                <tr>
                    <th>Local Ports Used</th>
                </tr>
                {{ streams:cycle namespace="store" stream="stores" limit="5" include="<?php echo $this->current_user->id;?>" include_by="created_by" }}
                <tr class="{{ odd_even }}">
                    <td>{{ local_port_used_local_port }}</td>
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

