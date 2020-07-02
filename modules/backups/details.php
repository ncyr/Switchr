<?php defined('BASEPATH') or exit('No direct script access allowed');
class Module_Backups extends Module
{
    public $version = '1.0';
    public function info()
    {
        $info = array(
            'name' => array(
                'en' => 'Backups'
            ),
            'description' => array(
                'en' => 'This is a PyroCMS Backups module'
            ),
            'frontend' => true,
            'backend' => true,
            'menu' => 'content',
            'roles'    => array(
            ),
            'sections' => array(
                'backups' => array(
                    'name'    => 'backups:create', // These are translated from our language file
                    'uri'    => 'admin/backups',
                    'shortcuts' => array(
                        'create' => array(
                            'name'    => 'backups:backup_source',
                            'uri'    => 'admin/backups/source/create',
                            'class' => 'add'
                        )
                    )
                ),
            )
        );
        return $info;
    }

    public function install()
    {
        $this->load->driver('Streams');
        $this->streams->utilities->remove_namespace('backups');
        $this->db->delete('settings', array('module'=>'backups'));

        $this->load->language('backups/backups');

        //Create our stream tables
        if (!$backup_dest_stream_id = $this->streams->streams->add_stream('Backup Destination', 'backup_dest', 'backups', 'backups_', null)) {
            return false;
        }
        if (!$hosts_stream = $this->streams->streams->get_stream('hosts', 'hosts')) {
            return false;
        }
        $fields = array(
            array(
                'name' => 'Host Assignment',
                'slug' => 'backup_dest_host_id',
                'namespace' => 'backups',
                'type' => 'relationship',
                'extra' => array('choose_stream' => $hosts_stream->id),
                'assign' => 'backup_dest',
            ),
            array(
                'name' => 'Destination Name',
                'slug' => 'backup_dest_name',
                'namespace' => 'backups',
                'type' => 'text',
                'extra' => array( 'max_length' => 50 ),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'Backup Type',
                'slug' => 'backup_dest_type',
                'namespace' => 'backups',
                'type' => 'text',
                'extra' => array(
                         'max_length' => 5, 'default_value' => 'local',
                         ),
                'assign' => 'backup_dest'
            ),
            array(
                'name' => 'Upload At',
                'slug' => 'backup_dest_uploadat',
                'namespace' => 'backups',
                'type' => 'text',
                'extra' => array( 'max_length' => 255 ),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'Username',
                'slug' => 'backup_dest_username',
                'namespace' => 'backups',
                'type' => 'encrypt',
                'extra' => array( 'max_length' => '25'),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'Password	',
                'slug' => 'backup_dest_password',
                'namespace' => 'backups',
                'type' => 'encrypt',
                'extra' => array( 'max_length' => '25', 'hide_typing' => 'yes' ),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'Hostname',
                'slug' => 'backup_dest_hostname',
                'namespace' => 'backups',
                'type' => 'text',
                'extra' => array( 'max_length' => 50 ),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'Port',
                'slug' => 'backup_dest_port',
                'namespace' => 'backups',
                'type' => 'integer',
                'extra' => array( 'max_length' => 20 ),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'Passive',
                'slug' => 'backup_dest_passive',
                'namespace' => 'backups',
                'type' => 'choice',
                'extra' => array(
                         'choice_type'=>'dropdown', 'choice_data'=> "1 : Active\n0 : Passive",
                         ),
                'assign' => 'backup_dest',
                'required' => true
            ),

            array(
                'name' => 'Backup Home Dir',
                'slug' => 'backup_dest_home',
                'namespace' => 'backups',
                'type' => 'text',
                'extra' => array('max_length' => '100'),
                'assign' => 'backup_dest',
                'required' => true
            ),array(
                'name' => 'Source (ie: C:\backups\*.txt)',
                'slug' => 'backup_dest_source',
                'namespace' => 'backups',
                'type' => 'textarea',
                'extra' => array('max_length' => '10000'),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'Destination',
                'slug' => 'backup_dest_dest',
                'namespace' => 'backups',
                'type' => 'textarea',
                'extra' => array('max_length' => '10000'),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'Limit (MB)',
                'slug' => 'backup_dest_limit',
                'namespace' => 'backups',
                'type' => 'integer',
                'extra' => array( 'max_length' => 20 ),
                'assign' => 'backup_dest'
            ),
            array(
                'name' => 'Paste Key (if using SFTP)',
                'slug' => 'backup_dest_ssh_key',
                'namespace' => 'backups',
                'type' => 'textarea',
                'extra' => array('max_length' => '5000'),
                'assign' => 'backup_dest',
            ),
            array(
                'name' => 'SSH Key Password (if used)',
                'slug' => 'backup_dest_ssh_password',
                'namespace' => 'backups',
                'type' => 'encrypt',
                'extra' => array( 'max_length' => '25', 'hide_typing' => 'yes' ),
                'assign' => 'backup_dest'
            ),

            array(
                'name' => 'Status',
                'slug' => 'backup_dest_status',
                'namespace' => 'backups',
                'type' => 'choice',
                'extra' => array(
                         'choice_type'=>'dropdown', 'choice_data'=> "1 : Active\n0 : Inactive",
                         ),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'Bucket Name',
                'slug' => 'backup_s3_bucketname',
                'namespace' => 'backups',
                'type' => 'text',
                'default_value' => 'FTP',
                'assign' => 'backup_dest',
                'is_unique' => true
            ),
            array(
                'name' => 'AWS Access Key ID',
                'slug' => 'backup_s3_awsaccesskeyid',
                'namespace' => 'backups',
                'type' => 'text',
                'extra' => array('max_length' => '50'),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'AWS Secret Key',
                'slug' => 'backup_s3_awssecretkey',
                'namespace' => 'backups',
                'type' => 'text',
                'extra' => array('max_length' => '50'),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'AWS Service URL',
                'slug' => 'backup_s3_serviceurl',
                'namespace' => 'backups',
                'type'    => 'choice',
                'extra' => array(
                         'choice_type'=>'dropdown', 'choice_data'=> "s3.amazonaws.com : s3.amazonaws.com", 'default_value' => "s3.amazonaws.com"
                         ),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'AWS Region Endpoint (ie: us-east-1)',
                'slug' => 'backup_s3_regionendpoint',
                'namespace' => 'backups',
                'type' => 'text',
                'extra' => array('max_length' => '50', 'default_value' => 'us-east-1'),
                'assign' => 'backup_dest',
                'required' => true
            ),
            array(
                'name' => 'Use MS Access Usernames as Foldernames',
                'slug' => 'backup_s3_msaccess_folders',
                'namespace' => 'backups',
                'type' => 'choice',
                'extra' => array(
                         'choice_type'=>'dropdown', 'choice_data'=> "1 : Yes\n0 : No", 'default_value' => "0"
                         ),
                'assign' => 'backup_dest',
            ),

        );

        //Load our field options up, ascertain fields in table.
        $this->streams->fields->add_fields($fields);
        //Hosts setup

        $this->streams->streams->update_stream('backup_dest', 'backups', array(
            'view_options' => array(
                            'backup_dest_host_id',
                            'backup_dest_name',
                            'backup_dest_uploadat',
                            'backup_dest_type',
                            'backup_dest_username',
                            'backup_dest_hostname',
                            'backup_dest_port',
                            'backup_dest_passive',
                            'backup_dest_source',
                            'backup_dest_dest',
                            'backup_dest_limit',
                            'backup_dest_ssh_key',
                            'backup_dest_ssh_password',
                            'backup_dest_home',
                            'backup_dest_status',
                            'backup_s3_host_id',
                            'backup_s3_bucketname',
                            'backup_s3_awsaccesskeyid',
                            'backup_s3_awssecretkey',
                            'backup_s3_serviceurl',
                            'backup_s3_regionendpoint',
                            'backup_s3_msaccess_folders'
                        )
        ));

        return true;
    }

    public function uninstall()
    {
        $this->load->driver('Streams');
        $this->streams->utilities->remove_namespace('backups');
        $this->db->delete('settings', array('module'=>'backups'));
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
