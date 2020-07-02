<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API module
 *
 * @author PyroCMS Dev Team
 * @package PyroCMS\Core\Modules\API
 */
class Module_Logs extends Module
{
    public $version = '0.1a';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Logging'
            ),
            'description' => array(
                'en' => 'Forward logging to the public without any technical knowledge. Switch them on and off, set your own rules.',
            ),
            'frontend' => true,
            'backend' => true,
            'menu' => 'content',
            'sections' => array(
                'logging' => array(
                    'name' => 'logging:logging',
                    'uri' => 'admin/logging	',
                    /*'shortcuts' => array(
                        array(
                            'name' => 'global:add',
                            'uri' => 'admin/logging/create',
                            'class' => 'add'
                        ),
                    ),*/
                ),
            ),
        );
    }

    public function install()
    {
        $this->load->driver('Streams');
        $this->load->language('logs/logging');
        $this->streams->utilities->remove_namespace('logging');
        $this->dbforge->drop_table('logging');

        //Create our stream tables
        if (! $logging_stream_id = $this->streams->streams->add_stream('lang:logging:logging', 'logging', 'logging', 'logging_', null)) {
            return false;
        }

        // Add some fields
        $fields = array(
            array(
                'name'      => 'lang:logging:controller',
                'slug'      => 'logging_controller',
                'assign'    => 'logging',
                'namespace' => 'logging',
                'type'      => 'text',
                'extra'     => array('max_length' => 30)
            ),
            array(
                'name'         => 'lang:logging:logging_host_id',
                'slug'         => 'logging_host_id',
                'assign'       => 'logging',
                'namespace'    => 'logging',
                'type'         => 'text',
                'extra'        => array('max_length' => 10),
                'title_column' => true,
           ),
           array(
                'name'      => 'lang:logging:logging_desc',
                'slug'      => 'logging_desc',
                'assign'    => 'logging',
                'namespace' => 'logging',
                'type'      => 'text',
                'extra'     => array('max_length' => 200),
                'required'  => true,

            ),
            array(
                'name'      => 'lang:logging:logging_ip',
                'slug'      => 'logging_ip',
                'assign'    => 'logging',
                'namespace' => 'logging',
                'type'      => 'text',
                'extra'     => array('max_length' => 15)

            ),
            array(
                'name'      => 'lang:logging:failure',
                'slug'      => 'logging_failure',
                'assign'    => 'logging',
                'namespace' => 'logging',
                'type'      => 'choice',
                'extra'     => array(
                    'max_length'    => 1,
                    'default_value' => '0',
                    'choice_type'   => 'radio',
                    'choice_data'   => "0 : Success\n1 : Failed",
                ),
                'required' => true
            )
        );

        $this->streams->fields->add_fields($fields);

        $this->streams->streams->update_stream('logging', 'logging', array(
            'view_options' => array(
                'logging_controller',
                'logging_host_id',
                'logging_ip',
                'logging_desc',
                'logging_failure'
            )
        ));

        return true;
    }

    public function uninstall()
    {
        $this->load->driver('Streams');
        $this->streams->utilities->remove_namespace('logging');

        return true;
    }

    public function upgrade($old_version)
    {
        return true;
    }
}
