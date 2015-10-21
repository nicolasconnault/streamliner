<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }
/**
*  Breeze Extended Profiler Library
*
* @author    PX Webdesign
* @link    http://lab.pxwebdesign.com.au
*
* This library extends the standard Profiler library with finding duplicate queries

*/

class MY_Profiler extends CI_Profiler
{
    function __construct()
    {
        parent::__construct();
    }

	function run()
	{
		$output = '<br clear="all" />';
		$output .= "<div style='background-color:#fff;padding:10px;'>";

		$output .= $this->_compile_memory_usage();
		$output .= $this->_compile_benchmarks();
		$output .= $this->_compile_uri_string();
		$output .= $this->_compile_get();
		$output .= $this->_compile_post();
		$output .= $this->_compile_duplicate_queries();
		$output .= $this->_compile_queries();

		$output .= '</div>';

		return $output;
	}

	function _compile_duplicate_queries() {
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #e01dc7;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";

		if ( ! class_exists('CI_DB_driver'))
		{
			$output .= '<legend style="color:#e01dc7;">&nbsp;&nbsp;DUPLICATE QUERIES&nbsp;&nbsp;</legend>';
			$output .= "\n";
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
			$output .="<tr><td width='100%' style='color:#e01dc7;font-weight:normal;background-color:#eee;'>".$this->CI->lang->line('profiler_no_db')."</td></tr>\n";
		}
		else
		{
			$queries['original'] = $this->CI->db->queries;
			$queries['unique'] = array_unique($this->CI->db->queries);
			$queries['duplicates'] = array_diff_assoc($queries['original'],$queries['unique']);
			$duplicateOutput = '';
			$duplicates = array();
			$duplicatesCount = array();
			// Append number of dupes
			if ($queries['duplicates']) {
				$highlight = array('SELECT', 'FROM', 'WHERE', 'AND', 'LEFT JOIN', 'ORDER BY', 'LIMIT', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'OR');
				// Build the duplicates array
				$i = 0;
				foreach ($queries['duplicates'] as $duplicateQuery) {
					if (is_numeric($key = array_search($duplicateQuery,$duplicates))) {
						// Found query so just increment
						$duplicatesCount[$key]++;
					} else {
						// Query not found so add and increment
						$duplicates[$i] = $duplicateQuery;
						$duplicatesCount[$i] = 2;
						$i++;
					}
				}
				foreach ($duplicates as $key => $val)
				{
					$val = htmlspecialchars($val, ENT_QUOTES);

					foreach ($highlight as $bold)
					{
						$val = str_replace($bold, '<strong>'.$bold.'</strong>', $val);
					}

					$duplicateOutput .= "<tr><td width='1%' valign='top' style='color:#990000;font-weight:normal;background-color:#ddd;'>[".$duplicatesCount[$key]."]&nbsp;&nbsp;</td><td style='color:#000;font-weight:normal;background-color:#ddd;'>".$val."</td></tr>\n";
				}
			}
			// Calculate number of dupes
			$duplicateNum = count($duplicates);
			$output .= '<legend style="color:#e01dc7;">&nbsp;&nbsp;DUPLICATE QUERIES ('.$duplicateNum.')&nbsp;&nbsp;</legend>';
			$output .= "\n";
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";

			if (count($this->CI->db->queries) == 0)
			{
				$output .= "<tr><td width='100%' style='color:#e01dc7;font-weight:normal;background-color:#eee;'>".$this->CI->lang->line('profiler_no_queries')."</td></tr>\n";
			}
			else
			{
				$output .= $duplicateOutput;
			}
		}

		$output .= "</table>\n";
		$output .= "</fieldset>";

		// If no dupes then don't output
		if (!count($queries['duplicates'])) {
			$output = '';
		}

		return $output;
	}

}
?>
