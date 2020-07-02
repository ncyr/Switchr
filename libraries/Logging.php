<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Logging
{
    public function create($controller, $desc = false, $host_id = null, $failure = null)
    {
        $ci = &get_instance();

        $ci->load->driver('Streams');

        if (is_null($desc)) {
            // Avoid breaking things if something bad happens
            $desc = 'Unknown logging';
        }

        $params = array(
            'logging_controller' => $controller,
            'logging_desc' => $desc,
            'logging_ip' => $_SERVER['REMOTE_ADDR'],
            'logging_host_id' => $host_id,
            'logging_failure' => $failure,
            'created' => date('Y-m-d h:i:s'),
            'created_by' => ($ci->current_user) ? $ci->current_user->id : '1'
        );

        if (!$ci->db->insert('logging_logging', $params)) {
            throw 'Sorry we could not insert a log.';
        } else {
            return true;
        }
    }
}
