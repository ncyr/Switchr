<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Error{

	public function logMsg($type, $controller, $desc, $failure=false, $noInsert=false, $level=1)
	{
		$ci =& get_instance();

		if (is_null($desc))
		{
			// Avoid breaking things if something bad happens
			$desc = 'Unknown error';
		}

		if (!$noInsert)
		{
			$store_id = $ci->session->userdata('current_store');
			
			$params = array(
				'user_id' => $ci->current_user->id,
				'store_id' => $store_id,
				'type' => $type,
				'controller' => $controller,
				'desc' => $desc,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'failure' => $failure,
				'level' => $level
			);

			if (!$ci->db->insert('error', $params))
			{
				throw new Exception('Could not insert log record');
			}
		}
	}
}
