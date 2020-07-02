<?php
class Servers_m extends MY_Model
{
    public function __construct()
    {
        parent::__construct();

        /**
         * If the hosts module's table was named "hostss"
         * then MY_Model would find it automatically. Since
         * I named it "hosts" then we just set the name here.
         */
        $this->_table = 'servers';
        $this->load->driver('Streams');
    }

    public function get_server($id)
    {
        $result = $this->streams->entries->get_entry($id, 'servers', 'servers');
        return $result;
    }

    public function getAllServers()
    {
        $params = array(
            'stream'    => 'servers',
            'namespace' => 'servers'
        );
        $result = $this->streams->entries->get_entries($params);
        return $result;
    }
}
