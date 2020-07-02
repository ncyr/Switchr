<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Sample Events Class
 *
 * @package     PyroCMS
 * @subpackage  Sample Module
 * @category    events
 * @author      PyroCMS Dev Team
 */
class Events_Servers
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->library('logging');
        Events::register('streams_post_insert_entry', array($this, 'stream_insert'));
    }

    public function stream_insert($trigger_data)
    {
        if ($trigger_data['stream']->stream_slug == 'servers') {
            $entry_id = $trigger_data['entry_id'];
            $stream = $trigger_data['insert_data'];
            $data = array(
                      'server_con_status' => 1
                      );
            if ($this->ci->db->update('servers_servers', $data)) {
                $this->ci->logging->create('Servers', "A new server has been added", $entry_id);
            } else {
                $this->ci->logging->create('Servers', 'A new port FAILED to be added', $entry_id, 1);
            }
        }
    }
    public function stream_update()
    {
        //$this->ci->logging->create( 'Servers', 'A server has been modified.', 1, 1, $trigger_data['entry_id'] );
    }
}
/* End of file events.php */
