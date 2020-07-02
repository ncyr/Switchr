<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_License extends Module
{
    public $version = '0.1';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Licensing'
            ),
            'description' => array(
                'en' => 'Licensing for the POSignite Client.',
            ),
            'frontend' => true,
            'backend' => true,
            'menu' => 'content',
            'sections' => array(
                'license_serials' => array(
                    'name' => 'license:license',
                    'uri' => 'admin/license',
                    'shortcuts' => array(
                        array(
                            'name' => 'global:add',
                            'uri' => 'admin/license/create',
                            'class' => 'add'
                        ),
                    ),
                ),
            ),
        );
    }

    public function install()
    {
        $this->load->driver('Streams');
        //$this->load->language('license/license');
        $this->streams->utilities->remove_namespace('license');

        //Create our stream tables
        if (! $license_stream_id = $this->streams->streams->add_stream('lang:license:license', 'license_serials', 'license', 'license_', null)) {
            return false;
        }

        $fields = array(
            /*
             * STORE USER ASSIGNMENT
             */
            array(
                'name' => 'License Serial',
                'slug' => 'license_serial',
                'namespace' => 'license',
                'type' => 'text',
                'extra' => array('max_length' => 19),
                'assign' => 'license_serials',
                'required' => true
            ),
            array(
                'name' => 'Expiration',
                'slug' => 'license_exp',
                'namespace' => 'license',
                'type' => 'datetime',
                'extra'    => array('input_type' => 'datepicker'),
                'assign' => 'license_serials',
                'required' => true
            ),
            array(
                'name' => 'Status',
                'slug' => 'license_status',
                'namespace' => 'license',
                'type' => 'choice',
                'extra' => array(
                         'choice_type'=>'dropdown', 'choice_data'=> "0 : Not Installed\n1 : Installed", 'default_value'=> '0',
                         ),
                'assign' => 'license_serials',
                'required' => true
            ),
        );

        $this->streams->fields->add_fields($fields);

        $this->streams->streams->update_stream('license_serials', 'license', array(
            'view_options' => array(
                            'license_serial',
                            'license_exp',
                            'license_status',
                        )
        ));

        return true;
    }

    public function uninstall()
    {
        $this->dbforge->drop_table('licenses');
        $this->load->driver('Streams');
        $this->streams->utilities->remove_namespace('license');

        $this->db->delete('settings', array('module'=>'license'));
        // TODO: Put a check in to see if something failed, otherwise it worked
        return true;
    }

    public function upgrade($old_version)
    {
        return true;
    }

    public function help()
    {
        return 'No documentation has been added for this module.<br />Contact the module developer for assistance.';
    }
}
