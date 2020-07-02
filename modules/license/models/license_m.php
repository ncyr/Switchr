<?php defined('BASEPATH') or exit('No direct script access allowed');

class License_m extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('Streams');
        $this->load->library('Logging');
    }

    protected function timestamps($data)
    {
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');

        return $data;
    }

    public function getHostByKey($key)
    {
        $license_entries = array(
            'stream' => 'license_serials',
            'namespace' => 'license',
            'where' => "license_serial='$key'",
            'limit' => 1
        );

        //were attempting to get the key, so we set the new status.
        $license = $this->streams->entries->get_entries($license_entries);
        $license_id = $license['entries'][0]['id'];

        $host_entries = array(
            'stream' => 'hosts',
            'namespace' => 'hosts',
            'where' => 'host_license='. $license_id,
            'limit' => 1
        );


        $host = $this->streams->entries->get_entries($host_entries);

        return $this->streams->entries->get_entry($host['entries'][0]['id'], 'hosts', 'hosts');
    }

    //	public function getServicesBySite($store_id)
    //	{
    //		$this->db->select('*');
    //		$this->db->from('license_license_serials as li');
    //		$this->db->join('licenses_status as lstat', 'li.id = lstat.license_id');
    //		$this->db->join('licenses_services as lserv', 'lserv.id = lstat.service_id');
//        $this->db->join('licenses_status_codes as lsc', 'lsc.id = lstat.status_code');
    //		$this->db->where('li.store_id = ' . $store_id);
    //		$query = $this->db->get();
    //		return $query->result();
    //	}
    //public function getServiceBySite($store_id, $service_id)
    //{
    //	$this->db->select('*');
    //	$this->db->from('licenses as li');
    //	$this->db->join('licenses_status as lstat', 'li.id = lstat.license_id');
    //	$this->db->join('licenses_services as lserv', 'lserv.id = lstat.service_id');
    //	$this->db->where('li.store_id = ' . $store_id);
    //	$this->db->where('lstat.service_id = ' . $service_id);
    //	$query = $this->db->get();
    //	$result = $query->result();
    //	return $result[0];
    //}
    //public function checkServiceBySite($service_id, $store_id)
    //{
    //	$services = $this->getServicesBySite($store_id);
    //	foreach($services as $service)
    //	{
    //		if(isset($service->service_id))
    //		{
    //			return true;
    //		}
    //		else{
    //			return false;
    //		}
    //	}
    //}

    public function getStoreLicense($storeId)
    {
        $query = $this->db->get_where('license_license_serials', array('store_id' => $storeId))->row();
        return $query;
    }

    public function removeLicense($key)
    {
        $result = $this->db->get_where('license_license_serials', 'license_serial="' . $key .'"')->row();
        $this->db->delete('license_license_serials', array('id' => $result->id));
    }

    public function changeStatus($key, $status)
    {
        $query = $this->db->get_where('license_license_serials', 'license_serial="' . $key .'"');
        $result = $query->result();
        $this->db->where('id', $result[0]->id);
        return $this->db->update('license_license_serials', array( 'license_status' => $status ));
    }

    public function assignLicense($host_id)
    {
        $site = SITE_REF;

        //make sure they own that record or are admin
        $this->host_id = $host_id;

        $entry_data = array(
            'stream' => 'hosts',
            'namespace' => 'hosts',
            'where' => $site . '_hosts_hosts.id=' . $host_id . ' AND ' . $site . '_hosts_hosts.created_by=' . $this->current_user->id,
            'limit' => 1
        );
        $host_record = $this->streams->entries->get_entries($entry_data);

        //they own this record
        if (count($host_record['entries']) > 0 || $this->current_user->group == 'admin') {
            $length = 19;
            $timestamp = strtotime('+1 week');
            $randomString = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);

            $data = array(
                'license_serial' => $randomString,
                'license_exp' => date('Y-m-d h:i:s', $timestamp),
                'license_status' => 0,
                'created' => date('Y-m-d h:i:s'),
                'created_by' => $this->current_user->id
            );
            $this->db->insert('license_license_serials', $data);
            $this->logging->create('Hosts', 'Host was assigned a license', $host_id);

            $license_entries = array(
                'stream' => 'license_serials',
                'namespace' => 'license',
                'where' => "license_serial='$randomString' AND " . $site . "_license_license_serials.created_by=" . $this->current_user->id,
                'limit' => 1
            );
            $license = $this->streams->entries->get_entries($license_entries);

            $update_data = array(
                'host_license' => $license['entries'][0]['id']
            );
            $this->db->where('id', $host_id);
            $this->db->update('hosts_hosts', $update_data);
        } else {
            //they do not own this record. hack attempt maybe for logging
            $this->logging->create('Hosts', 'Someone attempted to modify a license they do not own.', $host_id, 1);
        }
    }

    public function _removeLicense($id)
    {
        if ($this->streams->entries->delete_entry($id, 'license_serials', 'license')) {
            $this->logging->create('License', "Removed the license for $id.", $id);
        } else {
            $this->logging->create('License', "Could not remove the license id $id", $id, 1);
        }
    }
}
