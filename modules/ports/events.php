<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Sample Events Class
 *
 * @package     PyroCMS
 * @subpackage  Sample Module
 * @category    events
 * @author      PyroCMS Dev Team
 */
class Events_Ports
{
    protected $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        Events::register('streams_post_insert_entry', array($this, 'stream_insert'));
    }

    public function stream_insert($trigger_data)
    {
        if ($trigger_data['stream']->stream_slug == 'user_port') {
            $port_range_from = 33000;
            $port_range_to   = 43000;

            $entry_id = $trigger_data['entry_id'];
            $stream = $trigger_data['insert_data'];
            
            // If the node is down then we have to undo all of the fucked up shit
            // that Pyro just did for no reason.
            $this->ci->load->library('Connect');
            $this->ci->load->driver('Streams');
            $server_ip = $this->ci->streams->entries->get_entry(
                $trigger_data['insert_data']['server_id'],
                'servers',
                'servers',
                false
            )->server_ip;
            if (!$this->ci->connect->serverIsUp($server_ip)) {
                $this->ci->streams->entries->delete_entry(
                    $entry_id,
                    'user_port',
                    'ports'
                );
                echo "The server is down.";
                die(header("HTTP/1.0 500 Server Error"));
            }

            $port = $this->ci->connect->availablePort(
                $trigger_data['insert_data']['server_id'],
                $port_range_from,
                $port_range_to
            );

            $data = array(
                'remote_port' => $port,
                'is_active'    => '0'
            );

            $this->ci->db->where('id', $entry_id);
            if ($this->ci->db->update('ports_user_port', $data)) {
                $this->ci->load->library('Connect');
                $this->ci->connect->pushConfig($stream['host_id']);

                //$this->ci->logging->create( 'Ports', "A new port has been added for a host @ $port.", 1, 1, $entry_id );
            } else {
                //$this->ci->logging->create( 'Ports', 'A new port FAILED to be added for a host.', 1, 1, $entry_id, 1 );
            }
            redirect('/ports/index/'.$stream['host_id'], 'refresh');
        }
    }

    public function stream_update()
    {
        //$this->ci->logging->create( 'Ports', 'A port has been modified.', 1, 1, $trigger_data['entry_id'] );
    }
}
/* End of file events.php */
