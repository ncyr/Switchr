<?php defined('BASEPATH') or exit('No direct script access allowed');
class Module_reports extends Module
{
    public $version = '1.0b1';

    public function info()
    {
        $info = array(
            'name' => array(
                'en' => 'Reports',
            ),
            'description' => array(
                'en' => 'Get live reports from your POS system',
            ),
            'frontend' => false,
            'backend' => true,
            'menu' => 'content',
            'sections' => array(),
        );

        return $info;
    }

    public function install()
    {
        $this->dbforge->drop_table('reports');
        $this->db->delete('settings', array('module' => 'reports'));

        $reports_setting = array(
            'slug' => 'reports_setting',
            'title' => 'Reports Setting',
            'description' => 'Pick your POS type:',
            '`default`' => '1',
            '`value`' => '1',
            'type' => 'select',
            '`options`' => '1=Aloha|0=MICROS',
            'is_required' => 1,
            'is_gui' => 1,
            'module' => 'reports'
        );

        // Let's try running our DB Forge Table and inserting some settings
        if (! $this->db->insert('settings', $reports_setting)) {
            return false;
        }

        // No upload path for our module? If we can't make it then fail
        if (! is_dir($this->upload_path.'reports') and ! @mkdir($this->upload_path.'reports', 0777, true)) {
            return false;
        }

        $this->load->driver('Streams');
        $this->streams->utilities->remove_namespace('reports');

        //Create our stream tables
        if (! $reports_stream_id = $this->streams->streams->add_stream('Reports', 'aloha', 'reports', 'reports_', null)) {
            return false;
        }
        if (!$hosts_stream = $this->streams->streams->get_stream('hosts', 'hosts')) {
            return false;
        }

        $fields = array(
            array(
                'name' => 'Host ID',
                'slug' => 'host_id',
                'assign' => 'aloha',
                'namespace' => 'reports',
                'type' => 'relationship',
                'extra' => array('choose_stream' => $hosts_stream->id),
                'required' => true
            ),
            array(
                'name' => 'Report Name',
                'slug' => 'report_name',
                'namespace' => 'reports',
                'assign' => 'aloha',
                'type' => 'text',
                'required' => true
            ),
            array(
                'name' => 'Start Date',
                'slug' => 'start_date',
                'namespace' => 'reports',
                'assign' => 'aloha',
                'type' => 'datetime',
                'extra' => array('use_time' => 'no', 'storage' => 'unix'),
                'required' => true
            ),
            array(
                'name' => 'End Date',
                'slug' => 'end_date',
                'namespace' => 'reports',
                'assign' => 'aloha',
                'type' => 'datetime',
                'extra' => array('use_time' => 'no', 'storage' => 'unix'),
                'required' => true
            ),
            array(
                'name' => 'HTML Report',
                'slug' => 'report_html',
                'namespace' => 'reports',
                'assign' => 'aloha',
                'type' => 'textarea',
                'required' => true
            ),
            array(
                'name' => 'CSV Report',
                'slug' => 'report_csv',
                'namespace' => 'reports',
                'assign' => 'aloha',
                'type' => 'textarea',
                'required' => true
            ),
        );

        $this->streams->fields->add_fields($fields);

        $this->streams->streams->update_stream('aloha', 'reports', array(
            'view_options' => array(
                'report_setting',
                'start_date',
                'end_date',
            )
        ));

        return true;
    }

    public function uninstall()
    {
        $this->load->driver('Streams');
        $this->streams->utilities->remove_namespace('reports');
        $this->dbforge->drop_table('reports');
        $this->db->delete('settings', array('module'=>'reports'));
        // Put a check in to see if something failed, otherwise it worked
        return true;
    }

    public function upgrade($old_version)
    {
        // Your Upgrade Logic
        return true;
    }

    public function help()
    {
        // Return a string containing help info
        return 'No documentation has been written for this module yet.';
    }
}
/* End of file details.php */
