<?php defined('BASEPATH') or exit('No direct script access allowed');
class Module_Hosts extends Module
{
    public $version = '1.7.1';
    public function info()
    {
        $info = array(
            'name' => array(
                'en' => 'Hosts',
            ),
            'description' => array(
                'en' => 'This is a PyroCMS hosts module',
            ),
            'frontend' => true,
            'backend' => true,
            'menu' => 'content',
            'roles' => array(
            ),
            'sections' => array(
                'hosts' => array(
                    'name' => 'hosts:hosts', // These are translated from our language file
                    'uri' => 'admin/hosts',
                        'shortcuts' => array(
                            'create' => array(
                                'name' => 'hosts:create',
                                'uri' => 'admin/hosts/create',
                                'class' => 'add',
                            ),
                                array(
                                    'name' => 'hosts:assign_user',
                                    'uri' => 'admin/hosts/host_users/create',
                                    'class' => 'add',
                                ),
                                array(
                                    'name' => 'hosts:assign_group',
                                    'uri' => 'admin/hosts/host_group/create',
                                    'class' => 'add',
                                ),
                        ),
                ),
                'host_users' => array(
                        'name' => 'hosts:hosts_users',
                        'uri' => 'admin/hosts/host_users/index',
                            'shortcuts' => array(
                                'create' => array(
                                    'name' => 'hosts:assign',
                                    'uri' => 'admin/hosts/host_users/create',
                                    'class' => 'add',
                                    ),
                                ),
                        ),
                'host_groups' => array(
                        'name' => 'hosts:host_groups',
                        'uri' => 'admin/hosts/host_groups/index',
                            'shortcuts' => array(
                                'create' => array(
                                    'name' => 'hosts:assign',
                                    'uri' => 'admin/hosts/host_groups/create',
                                    'class' => 'add',
                                    ),
                                ),
                        ),
                ),

        );

        return $info;
    }

    public function install()
    {
        $this->load->driver('Streams');
        $this->load->language('hosts/hosts');
        $this->streams->utilities->remove_namespace('hosts');

        //Create our stream tables
        if (!$hosts_stream_id = $this->streams->streams->add_stream('lang:hosts:hosts', 'hosts', 'hosts', 'hosts_', null)) {
            return false;
        }
        if (!$host_users_stream_id = $this->streams->streams->add_stream('lang:hosts:host_users', 'host_users', 'hosts', 'hosts_', null)) {
            return false;
        }
        if (!$host_group_stream_id = $this->streams->streams->add_stream('lang:hosts:host_group', 'host_group', 'hosts', 'hosts_', null)) {
            return false;
        }
        if (!$host_band_stream_id = $this->streams->streams->add_stream('lang:hosts:host_band', 'host_band', 'hosts', 'hosts_', null)) {
            return false;
        }
        if (!$servers_stream = $this->streams->streams->get_stream('servers', 'servers')) {
            return false;
        }
        if (!$license_stream = $this->streams->streams->get_stream('license_serials', 'license')) {
            return false;
        }

        $fields = array(
            array(
                'name' => 'Host',
                'slug' => 'host_id',
                'namespace' => 'hosts',
                'type' => 'relationship',
                'extra' => array('choose_stream' => $hosts_stream_id),
                'assign' => 'host_users',
                'title_column' => true,
                'required' => true,
            ),
            array(
                'name' => 'User Assigned',
                'slug' => 'user_id',
                'namespace' => 'hosts',
                'type' => 'user',
                'assign' => 'host_users',
                'extra' => array('restrict_group' => 'corporate'),
            ),
            array(
                'name' => 'Host Description',
                'slug' => 'host_desc',
                'namespace' => 'hosts',
                'type' => 'text',
                'extra' => array('max_length' => '100'),
                'assign' => 'hosts',
                'required' => false,
                'title_column' => true,
            ),
            array(
                'name' => 'Host Group',
                'slug' => 'host_group',
                'namespace' => 'hosts',
                'type' => 'relationship',
                'extra' => array('choose_stream' => $host_group_stream_id),
                'assign' => 'hosts',
                'required' => false,
            ),
            array(
                'name' => 'Server Name',
                'slug' => 'host_server_id',
                'namespace' => 'hosts',
                'type' => 'relationship',
                'extra' => array('choose_stream' => $servers_stream->id),
                'assign' => 'hosts',
                'required' => true,
            ),
            array(
                'name' => 'Host User',
                'slug' => 'host_ssh_user',
                'namespace' => 'hosts',
                'type' => 'encrypt',
                'extra' => array('max_length' => '25', 'hide_typing' => 'yes'),
                'assign' => 'hosts',
                'required' => true,
                'is_unique' => true,
            ),
            array(
                'name' => 'SSH Password',
                'slug' => 'host_ssh_pass',
                'namespace' => 'hosts',
                'type' => 'encrypt',
                'extra' => array('max_length' => '25', 'hide_typing' => 'yes'),
                'assign' => 'hosts',
                'required' => true,
            ),
            array(
                'name' => 'Host Communications Port',
                'slug' => 'host_ssh_port',
                'namespace' => 'hosts',
                'type' => 'integer',
                'extra' => array('max_length' => 20),
                'assign' => 'hosts',
                'required' => true,
            ),
            array(
                'name' => 'Status',
                'slug' => 'host_status',
                'namespace' => 'hosts',
                'type' => 'integer',
                'extra' => array('default_value' => 0),
                'assign' => 'hosts',
            ),
            array(
                'name' => 'Last Online',
                'slug' => 'host_status_timestamp',
                'namespace' => 'hosts',
                'type' => 'text',
                'assign' => 'hosts',
            ),
            array(
                'name' => 'Host License',
                'slug' => 'host_license',
                'namespace' => 'hosts',
                'type' => 'relationship',
                'extra' => array('choose_stream' => $license_stream->id),
                'assign' => 'hosts',
                'is_unique' => true,
            ),
            array(
                'name' => 'Guac VNC ID',
                'slug' => 'host_guac_vnc_id',
                'namespace' => 'hosts',
                'type' => 'integer',
                'assign' => 'hosts',
            ),
            array(
                'name' => 'Guac RDP ID',
                'slug' => 'host_guac_rdp_id',
                'namespace' => 'hosts',
                'type' => 'integer',
                'assign' => 'hosts',
            ),
            array(
                'name' => 'Host Info',
                'slug' => 'host_info',
                'namespace' => 'hosts',
                'type' => 'text',
                'extra' => array('max_length' => '25000'),
                'assign' => 'hosts',
            ),
            array(
                'name' => 'Host Group Name',
                'slug' => 'host_group_name',
                'namespace' => 'hosts',
                'type' => 'text',
                'extra' => array('max_length' => '25'),
                'assign' => 'host_group',
                'is_unique' => true,
            ),
            array(
                'name' => 'Host Priority',
                'slug' => 'host_group_priority',
                'namespace' => 'hosts',
                'type' => 'choice',
                'extra' => array(
                         'choice_type' => 'dropdown', 'choice_data' => "1 : 1\n2 : 2\n23 : 3\n4 : 4\n5 : 5\n6 : 6\n7 : 7\n8 : 8\n9 : 9\n10 : 10",
                         ),
                'assign' => 'host_group',
                'is_unique' => true,
            ),
            array(
                'name' => 'User Assigned',
                'slug' => 'host_group_user_id',
                'namespace' => 'hosts',
                'type' => 'user',
                'assign' => 'host_group',
            ),
            array(
                'name' => 'Bandwidth Port',
                'slug' => 'host_band_port',
                'namespace' => 'hosts',
                'type' => 'integer',
                'extra' => array('max_length' => '10'),
                'assign' => 'host_band',
            ),
            array(
                'name' => 'Host Id',
                'slug' => 'host_band_host_id',
                'namespace' => 'hosts',
                'type' => 'integer',
                'extra' => array('max_length' => '10'),
                'assign' => 'host_band',
            ),
            array(
                'name' => 'Host Bandwidth Input',
                'slug' => 'host_band_input',
                'namespace' => 'hosts',
                'type' => 'integer',
                'assign' => 'host_band',
            ),
            array(
                'name' => 'Host Bandwidth Forward',
                'slug' => 'host_band_forward',
                'namespace' => 'hosts',
                'type' => 'integer',
                'assign' => 'host_band',
            ),
            array(
                'name' => 'Host Bandwidth Output',
                'slug' => 'host_band_output',
                'namespace' => 'hosts',
                'type' => 'integer',
                'assign' => 'host_band',
            ),

        );

        //Load our field options up, ascertain fields in table.
        $this->streams->fields->add_fields($fields);

        //Hosts setup
        $this->streams->streams->update_stream('hosts', 'hosts', array(
            'view_options' => array(
                            'host_desc',
                            'host_group',
                            'host_server_id',
                            'host_ssh_user',
                            'host_ssh_pass',
                            'host_ssh_port',
                            'host_status',
                            'host_license',
                            'host_info'
                        ),
        ));
        //Hosts setup
        $this->streams->streams->update_stream('host_users', 'hosts', array(
            'view_options' => array(
                            'host_id',
                            'user_id',
                        ),
        ));
        $this->streams->streams->update_stream('host_group', 'hosts', array(
            'view_options' => array(
                            'host_group_name',
                            'host_group_priority',
                            'host_group_user_id',
                        ),
        ));
        $this->streams->streams->update_stream('host_band', 'hosts', array(
            'view_options' => array(
                            'host_band_port',
                            'host_band_host_id',
                            'host_band_input',
                            'host_band_forward',
                            'host_band_output',
                        ),
        ));

        // No upload path for our module? If we can't make it then fail
        if (!is_dir($this->upload_path.'hosts') and !@mkdir($this->upload_path.'hosts', 0777, true)) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $this->load->driver('Streams');
        $this->streams->utilities->remove_namespace('stores');
        $this->db->delete('settings', array('module' => 'stores'));

        $this->streams->utilities->remove_namespace('hosts');

        $this->db->delete('settings', array('module' => 'hosts'));
        // TODO: Put a check in to see if something failed, otherwise it worked
        return true;
    }

    public function upgrade($old_version)
    {
        switch ($old_version) {
            case '1.1':
                $this->streams->fields->delete_field('host_info', 'hosts');
                $this->streams->fields->delete_field('host_status', 'hosts');
                $this->streams->fields->delete_field('host_status_timestamp', 'hosts');
                $fields = array(
                    array(
                        'name' => 'Host Info',
                        'slug' => 'host_info',
                        'namespace' => 'hosts',
                        'type' => 'text',
                        'extra' => array('max_length' => '25000'),
                        'assign' => 'hosts',
                    ),
                    array(
                        'name' => 'Status',
                        'slug' => 'host_status',
                        'namespace' => 'hosts',
                        'type' => 'integer',
                        'extra' => array('default_value' => 0),
                        'assign' => 'hosts',
                    ),
                    array(
                        'name' => 'Last Online',
                        'slug' => 'host_status_timestamp',
                        'namespace' => 'hosts',
                        'type' => 'text',
                        'assign' => 'hosts',
                    ),
                );
                //Load our field options up, ascertain fields in table.
                $this->streams->fields->add_fields($fields);

                //Hosts setup
                $this->streams->streams->update_stream('hosts', 'hosts', array(
                    'view_options' => array(
                                    'host_status',
                                    'host_info',
                                    'host_status_timestamp'
                                ),
                ));
                break;

            case '1.2':
                $this->streams->fields->delete_field('host_status_timestamp', 'hosts');
                $fields = array(
                    array(
                        'name' => 'Last Online',
                        'slug' => 'host_status_timestamp',
                        'namespace' => 'hosts',
                        'type' => 'text',
                        'assign' => 'hosts',
                    ),
                );
                //Load our field options up, ascertain fields in table.
                $this->streams->fields->add_fields($fields);

                //Hosts setup
                $this->streams->streams->update_stream('hosts', 'hosts', array(
                    'view_options' => array(
                        'host_status_timestamp'
                    ),
                ));
                break;

            case '1.3':
                $this->streams->fields->delete_field('host_guac_id', 'hosts');
                $fields = array(
                    array(
                        'name' => 'Guac VNC ID',
                        'slug' => 'host_guac_vnc_id',
                        'namespace' => 'hosts',
                        'type' => 'integer',
                        'assign' => 'hosts',
                    ),
                    array(
                        'name' => 'Guac RDP ID',
                        'slug' => 'host_guac_rdp_id',
                        'namespace' => 'hosts',
                        'type' => 'integer',
                        'assign' => 'hosts',
                    ),
                );
                //Load our field options up, ascertain fields in table.
                $this->streams->fields->add_fields($fields);

                //Hosts stream update.
                $this->streams->streams->update_stream('hosts', 'hosts', array());
                break;

            case '1.4':
                $current_fields = $this->streams->streams->get_assignments('profiles', 'users');
                foreach ($current_fields as $key => $field) {
                    if ($field->field_slug == 'timezone') {
                        $this->streams->fields->delete_field('timezone', 'users');
                    }
                }
                $fields = array(
                    'name' => 'Timezone',
                    'slug' => 'timezone',
                    'namespace' => 'users',
                    'assign' => 'profiles',
                    'type' => 'choice',
                    'extra' => array(
                        'choice_type' => 'dropdown',
                        'choice_data' => "America/Puerto_Rico : Puerto Rico (Atlantic)\nAmerica/New_York : New York (Eastern)\nAmerica/Chicago : Chicago (Central)\nAmerica/Denver : Denver (Mountain)\nAmerica/Phoenix : Phoenix (MST)\nAmerica/Los_Angeles : Los Angeles (Pacific)\nAmerica/Anchorage : Anchorage (Alaska)\nPacific/Honolulu : Honolulu (Hawaii)"
                    ),
                );
                $this->streams->fields->add_field($fields);
                break;

            case '1.5':
                $current_fields = $this->streams->streams->get_assignments('profiles', 'users');
                foreach ($current_fields as $key => $field) {
                    if ($field->field_slug == 'timezone') {
                        $this->streams->fields->delete_field('timezone', 'users');
                    }
                }
                $fields = array(
                'name' => 'Timezone',
                'slug' => 'timezone',
                'namespace' => 'users',
                'assign' => 'profiles',
                'type' => 'choice',
                'extra' => array(
                    'choice_type' => 'dropdown',
                    'choice_data' => "America/Puerto_Rico : Puerto Rico (Atlantic)\nAmerica/New_York : New York (Eastern)\nAmerica/Chicago : Chicago (Central)\nAmerica/Denver : Denver (Mountain)\nAmerica/Phoenix : Phoenix (MST)\nAmerica/Los_Angeles : Los Angeles (Pacific)\nAmerica/Anchorage : Anchorage (Alaska)\nPacific/Honolulu : Honolulu (Hawaii)"
                ),
                );
                $this->streams->fields->add_field($fields);
                break;

            case '1.6':
                $current_fields = $this->streams->streams->get_assignments('profiles', 'users');
                foreach ($current_fields as $key => $field) {
                    if ($field->field_slug == 'timezone') {
                        $this->streams->fields->delete_field('timezone', 'users');
                    }
                }
                $fields = array(
                'name' => 'Timezone',
                'slug' => 'timezone',
                'namespace' => 'users',
                'assign' => 'profiles',
                'type' => 'choice',
                'extra' => array(
                    'choice_type' => 'dropdown',
                    'choice_data' => "America/Puerto_Rico : Puerto Rico (Atlantic)\nAmerica/New_York : New York (Eastern)\nAmerica/Chicago : Chicago (Central)\nAmerica/Denver : Denver (Mountain)\nAmerica/Phoenix : Phoenix (MST)\nAmerica/Los_Angeles : Los Angeles (Pacific)\nAmerica/Anchorage : Anchorage (Alaska)\nPacific/Honolulu : Honolulu (Hawaii)"
                ),
                );
                $this->streams->fields->add_field($fields);
                break;

            case '1.6.1':
                $fields = array(
                'perm_ports'    => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                'perm_push'     => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                'perm_info'     => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                'perm_network'  => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                'perm_remove'   => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                'perm_reset'    => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                'perm_backup'   => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                'perm_reports'  => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                'perm_fixgrind' => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                'perm_connect'  => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                'perm_restart'  => array('type' => 'BOOLEAN', 'default' => 0, 'null' => false),
                );
                $this->dbforge->add_column('hosts_host_users', $fields);
                break;
            case '1.7.0':
                $current_fields = $this->streams->streams->get_assignments('profiles', 'users');
                foreach ($current_fields as $key => $field) {
                    if ($field->field_slug == 'timezone') {
                        $this->streams->fields->delete_field('timezone', 'users');
                    }
                }
                $fields = array(
                    'name' => 'Timezone',
                    'slug' => 'timezone',
                    'namespace' => 'users',
                    'assign' => 'profiles',
                    'type' => 'choice',
                    'extra' => array(
                        'choice_type' => 'dropdown',
                        'choice_data' => "America/Puerto_Rico : (GMT-04:00) Puerto Rico (Atlantic)\n
                                America/New_York : (GMT-05:00) New York (Eastern)\n
                                America/Chicago : (GMT-06:00) Chicago (Central)\n
                                America/Denver : (GMT-07:00) Denver (Mountain)\n
                                America/Phoenix : (GMT-07:00) Phoenix (MST)\n
                                America/Los_Angeles : (GMT-08:00) Los Angeles (Pacific)\n
                                America/Anchorage : (GMT-09:00) Anchorage (Alaska)\n
                                Pacific/Honolulu : (GMT-10:00) Honolulu (Hawaii)\n
                                America/Caracas : (GMT-04:30) Caracas\n
                                America/La_Paz : (GMT-04:00) La Paz\n
                                America/Santiago : (GMT-04:00) Santiago\n
                                Canada/Newfoundland : (GMT-03:30) Newfoundland\n
                                America/Buenos_Aires : (GMT-03:00) Buenos Aires\n
                                Greenland'>(GMT : (GMT-03:00) Greenland\n
                                Atlantic/Stanley : (GMT-02:00) Stanley\n
                                Atlantic/Azores : (GMT-01:00) Azores\n
                                Atlantic/Cape_Verde : (GMT-01:00) Cape Verde Is.\n
                                Africa/Casablanca : (GMT) Casablanca\n
                                Europe/Dublin : (GMT) Dublin\n
                                Europe/Lisbon : (GMT) Lisbon\n
                                Europe/London : (GMT) London\n
                                Africa/Monrovia : (GMT) Monrovia\n
                                Europe/Amsterdam : (GMT+01:00) Amsterdam\n
                                Europe/Belgrade : (GMT+01:00) Belgrade\n
                                Europe/Berlin : (GMT+01:00) Berlin\n
                                Europe/Bratislava : (GMT+01:00) Bratislava\n
                                Europe/Brussels : (GMT+01:00) Brussels\n
                                Europe/Budapest : (GMT+01:00) Budapest\n
                                Europe/Copenhagen : (GMT+01:00) Copenhagen\n
                                Europe/Ljubljana : (GMT+01:00) Ljubljana\n
                                Europe/Madrid : (GMT+01:00) Madrid\n
                                Europe/Paris : (GMT+01:00) Paris\n
                                Europe/Prague : (GMT+01:00) Prague\n
                                Europe/Rome : (GMT+01:00) Rome\n
                                Europe/Sarajevo : (GMT+01:00) Sarajevo\n
                                Europe/Skopje : (GMT+01:00) Skopje\n
                                Europe/Stockholm : (GMT+01:00) Stockholm\n
                                Europe/Vienna : (GMT+01:00) Vienna\n
                                Europe/Warsaw : (GMT+01:00) Warsaw\n
                                Europe/Zagreb : (GMT+01:00) Zagreb\n
                                Europe/Athens : (GMT+02:00) Athens\n
                                Europe/Bucharest : (GMT+02:00) Bucharest\n
                                Africa/Cairo : (GMT+02:00) Cairo\n
                                Africa/Harare : (GMT+02:00) Harare\n
                                Europe/Helsinki : (GMT+02:00) Helsinki\n
                                Europe/Istanbul : (GMT+02:00) Istanbul\n
                                Asia/Jerusalem : (GMT+02:00) Jerusalem\n
                                Europe/Kiev : (GMT+02:00) Kyiv\n
                                Europe/Minsk : (GMT+02:00) Minsk\n
                                Europe/Riga : (GMT+02:00) Riga\n
                                Europe/Sofia : (GMT+02:00) Sofia\n
                                Europe/Tallinn : (GMT+02:00) Tallinn\n
                                Europe/Vilnius : (GMT+02:00) Vilnius\n
                                Asia/Baghdad : (GMT+03:00) Baghdad\n
                                Asia/Kuwait : (GMT+03:00) Kuwait\n
                                Africa/Nairobi : (GMT+03:00) Nairobi\n
                                Asia/Riyadh : (GMT+03:00) Riyadh\n
                                Europe/Moscow : (GMT+03:00) Moscow\n
                                Asia/Tehran : (GMT+03:30) Tehran\n
                                Asia/Baku : (GMT+04:00) Baku\n
                                Europe/Volgograd : (GMT+04:00) Volgograd\n
                                Asia/Muscat : (GMT+04:00) Muscat\n
                                Asia/Tbilisi : (GMT+04:00) Tbilisi\n
                                Asia/Yerevan : (GMT+04:00) Yerevan\n
                                Asia/Kabul : (GMT+04:30) Kabul\n
                                Asia/Karachi : (GMT+05:00) Karachi\n
                                Asia/Tashkent : (GMT+05:00) Tashkent\n
                                Asia/Kolkata : (GMT+05:30) Kolkata\n
                                Asia/Kathmandu : (GMT+05:45) Kathmandu\n
                                Asia/Yekaterinburg : (GMT+06:00) Ekaterinburg\n
                                Asia/Almaty : (GMT+06:00) Almaty\n
                                Asia/Dhaka : (GMT+06:00) Dhaka\n
                                Asia/Novosibirsk : (GMT+07:00) Novosibirsk\n
                                Asia/Bangkok : (GMT+07:00) Bangkok\n
                                Asia/Jakarta : (GMT+07:00) Jakarta\n
                                Asia/Krasnoyarsk : (GMT+08:00) Krasnoyarsk\n
                                Asia/Chongqing : (GMT+08:00) Chongqing\n
                                Asia/Hong_Kong : (GMT+08:00) Hong Kong\n
                                Asia/Kuala_Lumpur : (GMT+08:00) Kuala Lumpur\n
                                Australia/Perth : (GMT+08:00) Perth\n
                                Asia/Singapore : (GMT+08:00) Singapore\n
                                Asia/Taipei : (GMT+08:00) Taipei\n
                                Asia/Ulaanbaatar : (GMT+08:00) Ulaan Bataar\n
                                Asia/Urumqi : (GMT+08:00) Urumqi\n
                                Asia/Irkutsk : (GMT+09:00) Irkutsk\n
                                Asia/Seoul : (GMT+09:00) Seoul\n
                                Asia/Tokyo : (GMT+09:00) Tokyo\n
                                Australia/Adelaide : (GMT+09:30) Adelaide\n
                                Australia/Darwin : (GMT+09:30) Darwin\n
                                Asia/Yakutsk : (GMT+10:00) Yakutsk\n
                                Australia/Brisbane : (GMT+10:00) Brisbane\n
                                Australia/Canberra : (GMT+10:00) Canberra\n
                                Pacific/Guam : (GMT+10:00) Guam\n
                                Australia/Hobart : (GMT+10:00) Hobart\n
                                Australia/Melbourne : (GMT+10:00) Melbourne\n
                                Pacific/Port_Moresby : (GMT+10:00) Port Moresby\n
                                Australia/Sydney : (GMT+10:00) Sydney\n
                                Asia/Vladivostok : (GMT+11:00) Vladivostok\n
                                Asia/Magadan : (GMT+12:00) Magadan\n
                                Pacific/Auckland : (GMT+12:00) Auckland\n
                                Pacific/Fiji : (GMT+12:00) Fiji\n"
                    ),
                );
                $this->streams->fields->add_field($fields);
                break;
        }
        return true;
    }

    public function help()
    {
        // Return a string containing help info
        return 'No documentation has been written for this module yet.';
    }
}
/* End of file details.php */
