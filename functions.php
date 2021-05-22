<?php

$quotes = [3.77, 15, 65, 300, 1500, 7000, 30000, 140000];


function list_filters($params, &$filters, &$binds, &$sort_field, &$sort_direction) {
	$filters = [];
	$binds = [];

	$result = get('result', $params);
	if (!is_null($result)) {
		$filters['result'] = $result ? 1 : 0;
	}

	// sort param
	$sort = get('sort', $params);
	switch ($sort) {
		case 'updated':
			$sort_field = 'updated_at';
			break;
		default:
			$sort_field = 'created_at';
			break;
	}

	// since
	$since = get('since', $params);
	if ($since) {
		if ($since_date = dt($since)) {
			$filters[] = 'created_at >= :since';
			$binds['since'] = $since_date;
		}
	}

	// direction param
	$direction = get('direction', $params, 'desc');
	$sort_direction = strtolower($direction) == 'asc' || strtolower($direction) == 'desc' ? strtoupper($direction) : 'DESC';
}

	function InitSampleTableData($cnt_ball, $quotes)
	{
		$sample_table_data = zeros([$cnt_ball, $cnt_ball]);
		for ($i = 0; $i < $cnt_ball; $i++) {
			for ($j = 0; $j <= $i; $j++) {
				$sample_table_data[$i][$j] = $quotes[$j] * factorialize($i + 1) / (factorialize($j + 1) * factorialize($i - $j));
			}
		}
		return $sample_table_data;
	}

	//helper function to create array of zeros
	function zeros($dimensions) {
	    $array = [];

	    for ($i = 0; $i < $dimensions[0]; ++$i) {
	        // array.push(dimensions.length == 1 ? 0 : zeros(dimensions.slice(1)));
	    }

	    return $array;
	}

	function factorialize($num) {
	  if ($num < 0) 
	        return -1;
	  else if ($num == 0) 
	      return 1;
	  else {
	      return ($num * factorialize($num - 1));
	  }
	}

	function getTableData($systems, $m, $quotes)
	{	
		$result = zeros([$m, $m]);
		$sample_data = InitSampleTableData($m, $quotes);
		for ($i = count($systems) - 1; $i >= 0; $i--) {
			$n = $systems[$i];
			for ($j = $n - 1; $j < $m; $j++) {
				$result[$j][$n - 1] = $sample_data[$j][$n - 1];
			}
		}
		return $result;
	}
	// print_r($newest_draw);
	function getCombinationSum($systems, $m)
	{
		$combination_sum = 0;
		for ($i = count($systems) - 1; $i >= 0; $i--) {
			$n = $systems[$i];
			if ( ! is_numeric($n)) {
				# code...
				break;
			}
			$combination = factorialize($m) / (factorialize($n) * factorialize($m - $n));
			$combination_sum += $combination;
		}
		return $combination_sum;
	}