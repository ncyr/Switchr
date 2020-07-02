
    <div id="body" class="section">
            {{ if user:logged_in }}
            <div class="panel panel-default">
                <div class="panel-heading"><h2>{{ template:title }}</h2> </div>
          	<input type="text" name="pickMonth" class="date-picker_month">
          	<div id="statement_content">
          		<ul>
          			<li>January-</li>
          			<li>February-</li>
          			<li>March-</li>
          			<li>April-</li>
          			<li>May-</li>
          			<li>June-</li>
          			<li>July-</li>
          			<li>August-</li>
          			<li>September-</li>
          			<li>October-</li>
          			<li>November-</li>
          			<li>December-</li>
          		</ul>
          	</div>
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

<div id="dialog" style="display: none;" title="">
    <p></p>
</div>