<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin controller for the api module
 *
 * @author		PyroCMS Dev Team
 * @package		PyroCMS\Core\Modules\API\Controllers
 */
class Ajax extends Public_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		if ( ! $this->input->is_ajax_request())
		{
			exit('Trickery is afoot.');
		}
	}
	
	public function index()
	{
		
	}

}

/* End of file admin.php */