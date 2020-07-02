<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Messages extends Public_Controller
{
	public function index()
	{
			$this->template
				->title($this->module_details['name'])
				->append_js('module::messages.js')
				->build('messages_index');
	}
	
}

/* End of file messages.php */