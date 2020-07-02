<div id="body" class="section">
    {{ if user:logged_in }}


    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{{ template:title }}</h2>
        </div>
        <p>You have successfully deleted your subscription.</p>

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
