<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @author 		PyroCMS Development Team
 * @package 	PyroCMS
 * @subpackage 	Controllers
 */
class Admin extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Reports_m');
		$this->data = array(
								'hosts' => $this->hosts_m->getHosts()
							);

	}

	public function index()
	{
		$this->data['message'] = '';
		$this->template->build('/admin/reports', $this->data);
	}

	/* AJAX Functions */
	public function ajax_report_show( $type = 'sales', $date1 = false, $date2 = false )
	{
		$this->Reports->reportShow($type, $date1, $date2);
	}

	//GET BY FIELD STUFF NOT QUITE THERE YET...
	//private function parse_lbr ()
	//{
	//	$data = array();
	//	$continue = true;
	//	for ($start = 6; $start < count($this->csv->csv_array()); $start += 21)
	//	{
	//		if (!$this->csv->get_cell($start + 2, 0, true))
	//			continue;
	//		$a = explode('  ', trim(str_replace(array('Emp', '*'), '', $this->csv->get_cell($start + 2, 0))));
	//
	//		$data[$a[0]] = array(
	//			'name' => $a[1],
	//			'total_cook' => $this->csv->get_cells($start + 9, 6, 15),
	//			'ttl' => $this->csv->get_cells($start + 17, 6, 15)
	//		);
	//	}
	//	$pos = $this->csv->get_cell_pos('totals:', 0);
	//	$data['totals'] = $this->csv->get_cells($pos['end'], 6, 15);
	//	$this->csv->add_array($data, 'payroll');
	//
	//	// The following line will get the summary, but all the same
	//	// information is included already, so I've left it out.
	//	//
	//	// $this->csv->add_rows('summary', 'end of report', 0, 13);
	//
	//	$this->load->view('/admin/lbr', $this->csv->debug_output());
	//}
	//
	//private function parse_pay ()
	//{
	//	$this->csv->add_rows('cash', 'visa', 0, 8);
	//	$this->csv->add_rows('visa', 'summary', 0, 8);
	//	$this->csv->add_rows('summary', 'comps', 0, 8, 1);
	//	$this->csv->add_rows('summary', 'promos', 0, 6, 2, 50, 'summary_siri');
	//	$this->csv->add_rows('promos', 'end of report', 0, 6, 4, 115, 'summary_promo');
	//
	//	$this->load->view('/admin/pay', $this->csv->debug_output());
	//}
	//
	//private function parse_pmx ()
	//{
	//	$this->csv->add_rows('non-sales categories', 'end of report', 0, 9);
	//	$this->csv->add_row('tot all alcohol:', 2, 9);
	//	$this->load->view('/admin/pmx', $this->csv->debug_output());
	//}
	//
	//private function parse_sls ()
	//{
	//	$this->csv->add_cell_right(7, 1);
	//	$this->csv->add_col('tax by tax id', 'total');
	//
	//	$this->csv->add_cols(array(
	//		'comps' => 'total',
	//		'voids' => 'total',
	//		'petty cash' => 'total',
	//		'guest count by day part' => 'total'
	//	));
	//	$this->csv->add_cols(array(
	//		'exempt taxables' => 'total',
	//		'promos' => 'total',
	//		'check count by day part' => 'total'
	//	), 4);
	//
	//	$this->csv->add_cell_right(80, 1);
	//	$this->csv->add_rows('sales by category', 'retail categories', 1, 7);
	//	$this->csv->add_rows('retail categories', 'totals', 1, 7);
	//	$this->csv->add_row('totals', 1, 7);
	//	$this->csv->add_cell_right(136, 1);
	//	$this->csv->add_rows('non-cash payments', 'petty cash', 1, 5);
	//	$this->csv->add_rows('tips by payment type', 'end of report', 1, 6);
	//
	//	$this->load->view('/admin/sls', $this->csv->debug_output());
	//}
}
