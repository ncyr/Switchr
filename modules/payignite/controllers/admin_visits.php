<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * POSignite Cart Module
 *
 * @author
 * @website
 * @package     PyroCMS
 * @subpackage
 */

class Admin_visits extends Admin_Controller
{


    // This will set the active section tab
    protected $section = 'visits';
    protected $data;
    public function __construct()
    {
        parent::__construct();
        $this->lang->load('payignite');
        $this->load->driver('Streams');
        $this->template->append_metadata('<script type="text/javascript" language="javascript">var stream_id='.$stream->id.'; var stream_offset='.$offset.'; var streams_module="'.$this->encrypt->encode($this->module_details['slug']).'";</script>');
        $this->template->append_js('streams/entry_sorting.js');
    }
    /**
     * List all items
     */
    public function index()
    {
        $extra['title'] = 'lang:payignite:visits';
        $extra['buttons'] = array(
            array(
            'label' => lang('global:edit'),
            'url' => 'admin/payignite/visits/edit/-entry_id-'
            ),
            array(
            'label' => lang('global:delete'),
            'url' => 'admin/payignite/visits/delete/-entry_id-',
            'confirm' => true
            )
        );
        $extra['columns'] = array(
            'created',
            'gym_id',
            'customer_id',
            'visit_count',
            'visit_discount',
            'visit_price',
            'visit_subscription',
            'visit_used_visits',
            'visit_paid',
        );
        //$extra['sorting'] = true;
        $this->streams->cp->entries_table('visits', 'payignite', 10, 'admin/payignite/visits/index', true, $extra);
    }
    public function create()
    {
        $extra = array(
            'return' => 'admin/payignite/visits',
            'success_message' => lang('dropship:submit_success'),
            'failure_message' => lang('dropship:submit_failure'),
            'title' => 'lang:payignite:create',
         );
        $this->streams->cp->entry_form('visits', 'payignite', 'new', null, true, $extra);
    }

    public function edit($id)
    {
        $extra = array(
            'return' => 'admin/payignite',
            'success_message' => lang('payignite:submit_success'),
            'failure_message' => lang('payignite:submit_failure'),
            'title' => 'lang:payignite:edit',
        );
        $this->streams->cp->entry_form('visits', 'payignite', 'edit', $id, true, $extra);
    }

    public function delete($id)
    {
        $this->streams->entries->delete_entry($id, 'visits', 'payignite');
        redirect('admin/payignite/visits', 'refresh');
    }
}
