<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * This is a sample module for PyroCMS.
 *
 * @author 		Jerel Unruh - PyroCMS Dev Team
 * @website		http://unruhdesigns.com
 */
class Plugin_Payignite extends Plugin
{
    /**
     * Item List
     * Usage:.
     *
     * {{ sample:items limit="5" order="asc" }}
     *      {{ id }} {{ name }} {{ slug }}
     * {{ /sample:items }}
     *
     * @return array
     */
    /*
    public function __construct()
    {
    }
    function gyms()
    {
        $params = array(
            'stream'    => 'gyms',
            'namespace' => 'payignite'
        );

        $data = $this->streams->entries->get_entries($params);
        return $data['entries'];
    }
    function city()
    {
        $params = array(
            'stream'    => 'city',
            'namespace' => 'payignite'
        );

        $data = $this->streams->entries->get_entries($params);
        return $data['entries'];
    }
    */
    public function subscription()
    {
        $cus_id = $this->attribute('cus_id');
        $sub_id = $this->attribute('sub_id');
        $result = $this->Payignite->getSubscription($this->attribute('cus_id'), $this->attribute('sub_id'));
        //echo $result->discount;
    }
}

/* End of file plugin.php */
