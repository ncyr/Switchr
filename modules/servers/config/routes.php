<?php  if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$route['servers/admin/servers(:any)'] = 'admin$1';
$route['servers/serverIsUp(:any)']    = '/servers/serverIsUp$1';
