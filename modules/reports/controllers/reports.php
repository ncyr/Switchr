<?php defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends Public_Controller
{
    protected $section = 'reports';

    public function __construct()
    {
        parent::__construct();

        $this->load->library('Logging');
        $this->load->driver('Streams');
        $this->load->library('Connect');
        $this->load->library('Report');
    }

    public function index($id = false, $mod = false)
    {
        $reportSettings = $this->report->getReportSettings($id);
        $reportDates    = $this->report->getReportDates($id);
        $this->template
                ->title($this->module_details['name'])
                ->set('host_id', $id)
                ->set('mod', $mod)
                ->set('reportSettings', $reportSettings)
                ->set('reportDates', $reportDates)
                ->set('user_email', $this->current_user->email)
                ->append_js('module::reports.js')->append_css('module::reports.css')
                ->build('reports_index');
    }

    public function create()
    {
        $this->template
                ->title('Add Report')
                ->build('reports_create');
    }

    public function edit($id)
    {
        $this->template->title(lang('global:edit'));

        $this->streams->cp->entry_form('reports', 'reports', 'edit', $id, true, array(
            'return'            => 'reports',
            'success_message'    => lang('reports:submit_success'),
            'failure_message'    => lang('reports:submit_failure'),
            'title'                => lang('global:add')
        ));
    }

    public function delete($id)
    {
        $this->streams->entries->delete_entry($id, 'reports', 'reports');
        redirect('/reports', 'refresh');
    }

    public function upload($id)
    {
        $this->template
                ->title($this->module_details['name'])
                ->append_js('module::reports.js')->append_css('module::reports.css')
                ->build('reports_upload');
    }

    public function statements($id=false)
    {
        $this->template
                ->title('Statements')
                ->append_js('module::reports.js')->append_css('module::reports.css')
                ->build('reports_statements');
    }

    //mod would be if aloha or micros etc. A1-4 will be args passed.
    public function ajax_show_doc($host_id, $mod, $type, $startDate = false, $endDate = false, $sendTo = false, $sendToNum = false)
    {
        switch ($mod) {
            case 'aloha':
                $this->load->model('hosts/hosts_m');
                $host = $this->hosts_m->get_host($host_id);
                // Extract type from filename. "Default.sls.exp" to "sls".
                $a1 = explode(".", $type)[1];
                $report = $this->report->generateReport(
                    $host_id,
                    array(
                        'mod' => $mod, 'type' => substr($type, -7, 3), 'startDate' => $startDate,
                        'endDate' => $endDate, 'sendTo' => $sendTo, 'sendToNum' => $sendToNum,
                        'host_ssh_port' => $host->host_ssh_port, 'reportSetting' => substr($type, 0, -4)
                    )
                );
                echo $report;
                break;
            default:
                break;
        }
    }

    public function ajax_statements($id=false)
    {
        $this->load->view('ajax_statements');
    }

    public function putLocalStatement($host_id)
    {
        if ($this->input->post()) {
            $file = $this->input->$_FILES['file']['tmp_name'];
            if ($host->created_by == $this->current_user->id) {
                $this->reports->putLocalStatement($host_id, $this->input->post('date'));
            }
        } else {
            redirect('/reports', 'refresh');
        }
    }
    public function resetPOS($host_id)
    {
        $this->reports->resetPOS($host_id);
        redirect('/reports', 'refresh');
    }
    public function fixWaitingGrind($host_id, $date)
    {
        $this->load->library('reports/aloha');
        $this->aloha->fixWaitingGrind($host_id, $date);
        redirect('/hosts', 'refresh');
    }
    public function settleBatch($host_id)
    {
        $this->load->library('reports/aloha');
        $this->aloha->settleBatch($host_id);
        redirect('/hosts', 'refresh');
    }
    public function getLastSettled($host_id)
    {
        $this->load->library('reports/aloha');
        echo $this->aloha->getLastSettled($host_id);
    }
    public function view_debug($host_id){
        $filename = $this->connect->scanHostDir($host_id, "c:\\temp");
        $this->template
                ->title($this->module_details['name'])
                ->set('host_id', $host_id)
                ->set('mod', $mod)
                ->set('filename', $filename)
                ->append_js('module::reports.js')->append_css('module::reports.css')
                ->build('reports_debug');
                
    }
    public function getDebugFile($host_id, $mod, $filename)
    {
        $this->load->library('reports/aloha');
        $debugFile = $this->aloha->getDebugFile($host_id, $filename);
        foreach($debugFile as $row){
            echo $row . "<br>";
        }
    }
}
/* End of file reports.php */
