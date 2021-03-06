<?php

	loadlib("whosonfirst_concordances");
	loadlib("whosonfirst_sources");

	loadlib("api_whosonfirst_output");

	########################################################################

	function api_whosonfirst_concordances_getSources(){

		$args = array();
		api_utils_ensure_pagination_args($args);

		$rsp = whosonfirst_concordances_get_sources($args);

		if (! $rsp['ok']){
			api_output_error(513);
		}

		$rows = $rsp['rows'];
		$pagination = $rsp['pagination'];

		$out = array(
			'sources' => $rows,
		);

		api_utils_ensure_pagination_results($out, $pagination);

		$more = array(
			'key' => 'sources',
		);

		api_output_ok($out, $more);
	}

	########################################################################

	function api_whosonfirst_concordances_getById(){

		$id = request_str("id");

		if (! $id){
			api_output_error(432);
		}

		$source = request_str("source");

		if (! $source){
			api_output_error(433);
		}

		# TO DO - ensure valid source here 

		$args = array();
		api_utils_ensure_pagination_args($args);

		$rsp = whosonfirst_concordances_get_by_id($source, $id, $args);

		if (! $rsp['ok']){
			api_output_error(513);
		}

		$rows = $rsp['rows'];
		$pagination = $rsp['pagination'];

		$out = array(
			'concordances' => $rows,
		);

		api_utils_ensure_pagination_results($out, $pagination);

		$more = array(
			'key' => 'concordances',
		);

		api_output_ok($out, $more);
	}

	########################################################################
	
	# the end