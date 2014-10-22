<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('array_to_csv'))
{

	/**
	 * 导出csv
	 *
	 * @param unknown_type $ad_id
	 * @return string
	 */
	function array_to_csv($table_title,$table_data,$split=",") {
		
		if (empty ( $table_data ) || count($table_data) == 0) 
        {
			//return "没有符合您要求的数据！^_^\nThere is no data!";
			return iconv ( 'utf-8', 'GB2312//IGNORE', "没有符合您要求的数据！^_^\nThere is no data!" );
		}
	
		$csv_title = iconv ( 'utf-8', 'GB2312//IGNORE',implode($split, $table_title)).PHP_EOL;
		
		$csv_body = "";
		foreach ($table_data as $row){
			if(is_array($row) && count($row) > 0) {
				foreach ($row as $key => $item) {
					if(is_array($item)) {
						$item = $item['count'];
					}
					$row[$key] = trim(str_replace($split,'',$item));
				}
			}
			$csv_body .= @ iconv ( 'utf-8', 'GB2312//IGNORE', implode($split, $row)).PHP_EOL;
		}
		
		$csv = $csv_title.$csv_body;
		
		return $csv;
		
	}
}

