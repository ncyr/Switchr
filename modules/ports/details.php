<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API module
 *
 * @author PyroCMS Dev Team
 * @package PyroCMS\Core\Modules\API
 */
class Module_Ports extends Module
{
    public $version = '0.2';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Ports'
            ),
            'description' => array(
                'en' => 'Forward ports to the public without any technical knowledge. Switch them on and off, set your own rules.',
            ),
            'frontend' => true,
            'backend' => true,
            'menu' => 'content',
            'sections' => array(
                'port_setup' => array(
                    'name' => 'ports:ports',
                    'uri' => 'admin/ports	',
                    'shortcuts' => array(
                        array(
                            'name' => 'global:add',
                            'uri' => 'admin/ports/create',
                            'class' => 'add'
                        ),
                    ),
                ),
                // 'remote_port_used' => array(
                // 	'name' => 'ports:remote_port_used',
                // 	'uri' => 'admin/ports/remote_port_used',
                // 	'shortcuts' => array(
                // 		array(
                // 		    'name' => 'global:add',
                // 		    'uri' => 'admin/ports/remote_port_used/create',
                // 		    'class' => 'add'
                // 		),
                // 	),
                // ),
            ),
        );
    }

    public function install()
    {
        $this->load->driver('Streams');
        $this->load->language('ports/ports');
        $this->streams->utilities->remove_namespace('ports');
        $this->dbforge->drop_table('ports_logs');
        $this->dbforge->drop_table('remote_port_logs');

        //Create our stream tables
        if (! $user_ports_stream_id = $this->streams->streams->add_stream('Ports', 'user_port', 'ports', 'ports_', null)) {
            return false;
        }
        //if ( ! $remote_port_used_stream_id = $this->streams->streams->add_stream('lang:ports:remote_port_used', 'remote_port_used', 'ports', 'ports_', null)) return false;
        if (! $hosts_stream = $this->streams->streams->get_stream('hosts', 'hosts')) {
            return false;
        }
        if (! $servers_stream = $this->streams->streams->get_stream('servers', 'servers')) {
            return false;
        }

        // Add some fields
        $fields = array(
            array(
                'name'            => 'Service Name',
                'slug'            => 'service_name',
                'assign'        => 'user_port',
                'namespace'        => 'ports',
                'type'            => 'text',
                'extra'            => array('max_length' => 40),
                'title_column'    => true,
                'required'        => true
            ),
            array(
                'name'            => 'Local Port',
                'slug'            => 'local_port',
                'assign'        => 'user_port',
                'namespace'        => 'ports',
                'type'            => 'integer',
                'extra'            => array('max_length' => 11),
                'required'        => true

            ),
            array(
                'name'            => 'Remote Port',
                'slug'            => 'remote_port',
                'assign'        => 'user_port',
                'namespace'        => 'ports',
                'type'            => 'integer',
                'extra'            => array('max_length' => 11),
                'required'        => true,
                'is_unique'        => true

            ),
            array(
                'name'            => 'Server Name',
                'slug'            => 'server_id',
                'assign'        => 'user_port',
                'namespace'        => 'ports',
                'type'            => 'relationship',
                'extra'            => array('choose_stream' => $servers_stream->id ),
                'required'        => true
            ),
            array(
                'name'            => 'Host Name',
                'slug'            => 'host_id',
                'assign'        => 'user_port',
                'namespace'        => 'ports',
                'type'            => 'relationship',
                'extra'            => array('choose_stream' => $hosts_stream->id ),
                'required'        => true
            ),
            array(
                'name'            => 'IP Rule',
                'slug'            => 'ip_rule',
                'assign'        => 'user_port',
                'namespace'        => 'ports',
                'type'            => 'text',
                'extra'            => array('max_length' => 40, 'default_value'=> '0.0.0.0/0'),
                'required'        => true
            ),
            array(
                'name'            => 'MAC Rule',
                'slug'            => 'mac_rule',
                'assign'        => 'user_port',
                'namespace'        => 'ports',
                'type'            => 'text',
                'extra'            => array('max_length' => 40),
            ),
            array(
                'name'            => 'Protocol',
                'slug'            => 'protocol',
                'assign'        => 'user_port',
                'namespace'        => 'ports',
                'type'            => 'choice',
                'extra'            => array('choice_type'=>'dropdown', 'choice_data'=> "TCP : TCP\nUDP : UDP", 'default_value'=>"TCP"),
                'required'        => true
            ),
            array(
                'name'            => 'Status',
                'slug'            => 'is_active',
                'assign'        => 'user_port',
                'namespace'        => 'ports',
                'type'            => 'choice',
                'extra'            => array('choice_type'=>'dropdown', 'choice_data'=> "1 : On\n0 : Off", 'default_value'=>"0"),
                'required'        => true
            ),
        );

        //Load our field options up, ascertain fields in table.
        $this->streams->fields->add_fields($fields);

        //Stores setup
        $this->streams->streams->update_stream('user_port', 'ports', array(
            'view_options' => array(
                            'service_name',
                            'local_port',
                            'server_id',
                            'host_id',
                            'ip_rule',
                            'mac_rule',
                            'protocol',
                            'is_active',

                        )
            ));
        //remote ports
        //$this->streams->streams->update_stream('remote_port_used', 'ports', array(
        //	'view_options' => array(
        //					'remote_port_used_remote_port',
        //					'remote_port_is_active',
        //					'remote_port_server_location'
        //				)
        //	));
        // Create Port Log table
        $this->dbforge
            ->add_field(array(
                'id' => array('type' => 'int', 'constraint' => 11, 'auto_increment' => true),
                'local_port' => array('type' => 'int', 'constraint' => 11),
                'remote_port' => array('type' => 'int', 'constraint' => 11),
                'status' => array('type' => 'int', 'constraint' => 1),
                'ip_address' => array('type' => 'varchar', 'constraint' => 15),
                'time' => array('type' => 'varchar', 'constraint' => 11),
            ))
            // Make the key Primary (thats what true does)
            ->add_key('id', true)
            // Now build it!
            ->create_table('remote_port_logs');

        // Create Logging table
        $this->dbforge
            ->add_field(array(
                'id' => array('type' => 'int', 'constraint' => 11, 'auto_increment' => true),
                'uri' => array('type' => 'varchar', 'constraint' => 255),
                'method' => array('type' => 'varchar', 'constraint' => 6),
                'params' => array('type' => 'text', 'null' => true),
                'api_key' => array('type' => 'varchar', 'constraint' => 40),
                'ip_address' => array('type' => 'varchar', 'constraint' => 15),
                'time' => array('type' => 'int', 'constraint' => 11),
                'authorized' => array('type' => 'tinyint', 'constraint' => 1),
            ))
            // Make the key Primary (thats what true does)
            ->add_key('id', true)
            // Now build it!
            ->create_table('ports_logs');

        $this->db->delete('settings', array('module' => 'ports'));

        $ports = array(
            'id' => array(
            'type' => 'INT',
                'constraint' => '11',
                'auto_increment' => true
            ),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'slug' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
        );

        $ports_setting = array(
            'slug' => 'ports_port_range',
            'title' => 'Ports Port Range',
            'description' => 'Specify a range to make available to clients',
            'default' => '50000',
            'value' => '50000',
            'options' => false,
            'type' => 'text',
            'is_required' => 1,
            'is_gui' => 1,
            'module' => 'ports'
        );

        $this->dbforge->add_field($ports);
        $this->dbforge->add_key('id', true);
        $this->db->insert('settings', $ports_setting);

        return true;
    }

    public function uninstall()
    {
        $this->load->driver('Streams');
        $this->streams->utilities->remove_namespace('ports');

        $this->dbforge->drop_table('ports_logs');
        $this->dbforge->drop_table('remote_port_logs');

        $this->db->delete('settings', array('module' => 'sample'));

        return true;
    }

    public function upgrade($old_version)
    {
        //v1.0->1.1
        if ($old_version == "0.1a") {
            $old_version == "0.2";
            $fields =
            array(
                'name'            => 'MAC Rule',
                'slug'            => 'mac_rule',
                'assign'        => 'user_port',
                'namespace'        => 'ports',
                'type'            => 'text',
                'extra'            => array('max_length' => 40),
            );
            $this->streams->streams->update_stream('user_port', 'ports', array(
                'view_options' => array(
                                'mac_rule'
                            )
                ));
            return true;
        }
        //end v1.1 upgrade
        return false;
    }
}
