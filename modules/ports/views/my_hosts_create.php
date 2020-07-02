<div class="content">
    <h2>{{ template:title }}</h2>
    <div class="section">
        <fieldset>
            {{ if user:logged_in }}
            {{ streams:form namespace="store" stream="stores" limit="5" mode="new" creator_only="yes" }}
                {{ form_open }}
                <table>
                    {{ fields }}
                    <td> {{ input_title }}</td>
                    <tr class="{{ odd_even }}">
                        <td> {{ input }}</td>
                    </tr>
                    {{ /fields }}
                </table>
                {{ form_submit }}&nbsp;<button onclick="window.history.go(-1)">Back</button>
                {{ form_close }}
            {{ /streams:form }}
           <p>{{ error }}{{ input }}</p>
            {{ else }}
            <p><a href="users/login">Please login first.</a></p>
            {{ endif }}
        </fieldset>
    </div>
</div>

