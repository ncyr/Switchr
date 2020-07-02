<h3>Your <?= $service->service_name ?> Subscription</h3>
<ul>
	<li>Current Status: <?=$service->status ? 'Inactive' : 'Active'; ?></li>
	<li>Expires: <?=$service->expiration ? : $service->expiration; ?></li>
</ul>