<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cart extends Public_Controller
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->lang->load('payignite');
        //$this->load->library('stripe/stripe');
        $this->load->model('payignite_m', 'Payignite');
        $this->load->driver('Streams');
        $this->template->append_css('module::payignite.css');
    }
    /**
     * List all CARTs.
     *
     * We are using the Streams API to grab
     * data from the payignites database. It handles
     * pagination as well.
     */
    public function index()
    {
        $params = array(
            'stream' => 'payignite',
            'namespace' => 'payignite',
            'paginate' => 'yes',
            'pag_segment' => 4,
        );

        $this->data->payignite = $this->streams->entries->get_entries($params);

        // Build the page
        $this->template->title($this->module_details['name'])
            ->build('index', $this->data);
    }
}

/* End of file payignite.php */
