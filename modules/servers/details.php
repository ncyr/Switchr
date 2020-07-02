<?php defined('BASEPATH') or exit('No direct script access allowed');
class Module_Servers extends Module
{
    public $version = '1.0';
    public function info()
    {
        $info = array(
            'name' => array(
                'en' => 'Servers'
            ),
            'description' => array(
                'en' => 'This is a PyroCMS servers module'
            ),
            'frontend' => false,
            'backend'  => true,
            'menu'     => 'content',
            'roles'    => array(),
            'sections' => array(
                'servers' =>
                    array(
                        'name' => 'servers:servers', // These are translated from our language file
                        'uri'  => 'admin/servers',
                        'shortcuts' => array(
                            'create' => array(
                                'name'  => 'servers:create',
                                'uri'   => 'admin/servers/create',
                                'class' => 'add'
                            ),
                        )
                    ),
                    array(
                        'name' => 'servers:upload_key', // These are translated from our language file
                        'uri'  => 'admin/servers/upload_key',
                        'shortcuts' => array(
                            'create' => array(
                                'name'  => 'servers:upload_key',
                                'uri'   => 'admin/servers/upload_key',
                                'class' => 'add'
                            ),
                        )
                    ),
            )
        );

        return $info;
    }


    public function install()
    {
        $this->load->driver('Streams');
        $this->load->language('servers/servers');
        $this->streams->utilities->remove_namespace('servers');

        //Create our stream tables
        if (! $servers_stream_id = $this->streams->streams->add_stream('Servers', 'servers', 'servers', 'servers_', null)) {
            return false;
        }

        //!- Here we create the field options we're going to use.
        $fields = array(
            array(
                'name' => 'Server Name',
                'slug' => 'server_name',
                'namespace' => 'servers',
                'type' => 'text',
                'extra' => array('max_length' => '25'),
                'assign' => 'servers',
                'required' => true,
                'title_column' => true
            ),
            array(
                'name' => 'Server IP',
                'slug' => 'server_ip',
                'namespace' => 'servers',
                'type' => 'text',
                'extra' => array('max_length' => '15'),
                'assign' => 'servers',
                'required' => true
            ),
            array(
                'name' => 'Server User Name',
                'slug' => 'server_username',
                'namespace' => 'servers',
                'type' => 'encrypt',
                'extra' => array('max_length' => '30', 'hide_typing' => 'yes'),
                'assign' => 'servers',
                'required' => true
            ),
            array(
                'name' => 'Server Password',
                'slug' => 'server_password',
                'namespace' => 'servers',
                'type' => 'encrypt',
                'extra' => array('max_length' => '30', 'hide_typing' => 'yes'),
                'assign' => 'servers',
                'required' => true
            ),
            array(
                'name' => 'SSH Key Password',
                'slug' => 'server_key_password',
                'namespace' => 'servers',
                'type' => 'encrypt',
                'extra' => array('max_length' => '30', 'hide_typing' => 'yes'),
                'assign' => 'servers',
            ),
            array(
                'name' => 'Connection',
                'slug' => 'server_con_status',
                'namespace' => 'servers',
                'assign'    => 'servers',
                'type' => 'integer',
                'extra' => array('max_length'=> 1),
            ),
        );

        //Load our field options up, ascertain fields in table.
        $this->streams->fields->add_fields($fields);

        //Servers setup
        $this->streams->streams->update_stream('servers', 'servers', array(
            'view_options' => array(
                'server_name',
                'server_ip',
                'server_username',
                'server_password',
                'server_key_password',
                'server_con_status'
            )
        ));


        // No upload path for our module? If we can't make it then fail
        if (! is_dir($this->upload_path.'servers') and ! @mkdir($this->upload_path.'servers', 0777, true)) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $this->load->driver('Streams');

        $this->streams->utilities->remove_namespace('servers');

        $this->db->delete('settings', array('module'=>'servers'));
        // TODO: Put a check in to see if something failed, otherwise it worked
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
