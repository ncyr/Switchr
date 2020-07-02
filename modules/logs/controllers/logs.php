<?php defined('BASEPATH') or exit('No direct script access allowed');

class Logs extends Public_Controller
{
    //protected $section = 'logging';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('hosts/hosts_m');
    }

    public function index()
    {
        $this->template
            ->title($this->module_details['name'])
            ->build('index');
    }
}

/* End of file logging.php */
