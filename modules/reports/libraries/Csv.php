<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Csv {
	private $csv = array();
	private $debug = array();
	private $data = array();
	private $results = array();

	public function __construct($params = array())
	{
		if (array_key_exists('file', $params))
		{
			$this->load_csv($params['file']);
		}
	}

	public function load_csv ($file)
	{
		$this->debug[] = 'Loading csv from ' . $file;
		$handle = fopen($file, FOPEN_READ);
		$line = 0;

		while ($data = fgetcsv($handle))
		{
			$line++;
			$this->csv[$line] = $data;
		}

		fclose($handle);

		return true;
	}

	public function csv_array ()
	{
		return $this->csv;
	}

	public function add_array ($array, $key)
	{
		$this->data[$key] = $array;
	}

	public function add_cell ()
	{
		$name = func_get_arg(0);

		if (func_num_args() == 3)
		{
			$row = func_get_arg(1);
			$col = func_get_arg(2);

			$this->data[$name] = $this->csv[$row][$col];

			return true;
		}
		elseif (func_num_args() == 2)
		{
			$val = func_get_arg(1);
			$this->data[$name] = $val;

			return true;
		}

		return false;
	}

	public function add_cell_right ($row, $col)
	{
		$key = $this->csv[$row][$col];
		$this->data[$key] = @$this->csv[$row][$col + 1];

		return true;
	}

	public function add_col ($from, $to, $col = 1, $start_row = 1)
	{
		if ($this->search($from, $to, $col, $start_row))
		{
			$this->data[$from] = $this->getdata($this->results[$from], $this->csv);
		}
	}

	public function add_cols ($keywords, $col = 1, $start_row = 1)
	{
		foreach ($keywords as $from => $to)
		{
			$this->add_col($from, $to, $col, $start_row);
		}
	}

	public function add_row ($search, $start_col, $end_col)
	{
		$data = array();
		$result = $this->search($search, null, $start_col);

		for ($col = $start_col + 1; $col <= $end_col; $col++)
		{
			$data[] = $this->get_cell($result['start'], $col);
		}
		$this->data[$search] = $data;
	}

	public function add_rows ($from, $to, $start_col, $end_col, $data_indent = 0, $start_row = 1, $save_as = null)
	{
		$data = array();
		$result = $this->search($from, $to, $start_col, $start_row);

		if ($data_indent !== 0)
		{
			$start_col += $data_indent;
		}

		for ($row = $result['start']; $row < $result['end']; $row++)
		{
			if ($data_indent === 0 && !$this->get_cell($row, $start_col, true))
			{
				continue;
			}

			$key = $this->get_cell($row, $start_col);
			if (trim(str_replace('-', '', $key)) == '' && trim(str_replace('-', '', $this->get_cell($row, $start_col+1))) == '')
			{
				$this->debug[] = 'First cell contains only dashes and/or spaces, or is blank. Skipping row...';
				continue;
			}

			for ($col = $start_col + 1; $col <= $end_col; $col++)
			{
				$data[$key][] = $this->get_cell($row, $col);
			}
		}

		$k = $save_as ? $save_as : $from;
		$this->data[$k] = $data;
	}

	public function get_cell ($row, $col, $strict_check = false)
	{
		if (!array_key_exists($row, $this->csv)
		 || !array_key_exists($col, $this->csv[$row])
		 || ($strict_check && trim($this->csv[$row][$col]) == ''))
		{
			return false;
		}

		return $this->csv[$row][$col];
	}

	public function get_cells ($row, $start, $end)
	{
		$data = array();
		for ($i = $start; $i < $end; $i++)
		{
			$data[] = $this->get_cell($row, $i);
		}

		return $data;
	}

	public function get_cell_pos ($word, $col = 1, $start_row = 1)
	{
		return $this->search($word, null, $col, $start_row);
	}

	private function search ($word, $end = null, $col = 1, $start_row = 1)
	{
		$search = $word;
		$found = false;

		for ($i = $start_row; $found == false; $i++)
		{
			$this->debug[] = 'Searching column ' . $col . ' of line ' . $i . ' for ' . $search;
			if (!array_key_exists($i, $this->csv))
			{
				if (array_key_exists($word, $this->results) && array_key_exists('start', $this->results[$word]))
				{
					$status = 'found start but not end. Setting end to last row.';
					$this->results[$word]['end'] = $i - 1;
					$found = $this->results[$word];
				}
				else
				{
					$status = 'failed to find search term.';
					$found = null;
				}
				$this->debug[] = 'Reached end of file: ' . $status;

				return $found;
			}
			if (!array_key_exists($col, $this->csv[$i]))
			{
				$this->debug[] = 'Column doesn\'t exist in this row, skipping...';
				continue;
			}

			if (trim(strtolower(str_replace('*', '', $this->csv[$i][$col]))) == $search)
			{
				if (!array_key_exists($word, $this->results))
				{
					$this->results[$word] = array('col' => $col);
				}

				if ($search == $word)
				{
					if ($end === null)
					{
						$tmp = array_merge($this->results[$word], array('start' => $i, 'end' => $i+1));
						$this->debug[] = 'Found ' . $word . ' on line ' . $i;

						return $tmp;
					}
					$this->results[$word]['start'] = $i + 1;
					$search = $end;
					$this->debug[] = 'Found start of '.$word.' on line '.$i;
				}
				else
				{
					$this->results[$word]['end'] = $i;
					$found = $this->results[$word];
					$this->debug[] = 'Found end of '.$word.' on line '.$i;
				}
			}
			else $this->debug[] = 'Nothing found';
		}
		return $found;
	}

	private function getdata($params)
	{
		extract($params);
		$data = array();

		for ($i = $start; $i < $end; $i++)
		{
			if (trim($this->csv[$i][$col]) == '')
			{
				continue;
			}
			$key = strtolower(str_replace(array(' ', '/'), array('_', ''), trim($this->csv[$i][$col])));
			$data[$key] = $this->csv[$i][$col+1];
		}
		return $data;
	}

	public function debug_output ()
	{
		return array('data' => array(
			'parsed' => $this->data,
			'results' => $this->results,
			'debug' => $this->debug
		));
	}
}
