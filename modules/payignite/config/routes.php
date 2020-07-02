<?php  if (!defined('BASEPATH')) {
     exit('No direct script access allowed');
 }

//$route['payignite/admin/streams(:any)'] = 'admin_streams$1';

$route['payignite/admin/payments(/:any)?'] = 'admin_payments$1';
$route['payignite/admin/coupons(/:any)?'] = 'admin_coupons$1';
$route['payignite/admin/plans(/:any)?'] = 'admin_plans$1';
$route['payignite/admin/customers(/:any)?'] = 'admin_customers$1';
$route['payignite/admin/subscriptions(/:any)?'] = 'admin_subscriptions$1';
//$route['payignite/admin/gyms(/:any)?'] = 'admin_gyms$1';
//$route['payignite/admin/city(/:any)?'] = 'admin_city$1';
//$route['payignite/admin/visits(/:any)?'] = 'admin_visits$1';

$route['payignite/admin(/:any)?'] = 'admin$1';

$route['payignite/(/:any)?'] = '$1';  // allow redirect to Payment from Hosts

