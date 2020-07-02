<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Sample Events Class
 *
 * @package     PyroCMS
 * @subpackage  Sample Module
 * @category    events
 * @author      PyroCMS Dev Team
 */
class Events_Backups
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        Events::register('streams_post_insert_entry', array($this, 'stream_insert'));
        Events::register('streams_post_update_entry', array($this, 'stream_update'));
    }

    public function stream_insert($trigger_data)
    {
        $stream = $trigger_data['insert_data'];
        $entry_id = $trigger_data['entry_id'];

        if ($trigger_data['stream']->stream_slug == 'backup_dest') {
            $this->ci->load->model('hosts/hosts_m');
            $this->ci->load->model('backups_m');
            $this->ci->load->model('license/license_m');

            $this->ci->backups_m->pushConfig($trigger_data['entry_id'], $trigger_data['insert_data']['backup_dest_host_id'], $trigger_data['insert_data']['backup_dest_type']);
        }
    }

    public function stream_update($trigger_data)
    {
        if ($trigger_data['stream']->stream_slug == 'backup_dest') {
            $this->ci->load->model('backups_m');

            $this->ci->backups_m->pushConfig($trigger_data['entry_id'], $trigger_data['update_data']['backup_dest_host_id'], $trigger_data['update_data']['backup_dest_type']);
        }
    }
}
/* End of file events.php */
