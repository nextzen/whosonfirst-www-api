<?php

	include("include/init.php");

	loadlib("api");
	loadlib("api_spec");
	loadlib("api_methods");
	loadlib("api_oauth2_access_tokens");
	
	features_ensure_enabled(array(
		"api",
		"api_documentation",
		"api_explorer",
	));

	if ($GLOBALS['cfg']['api_require_loggedin']){
		login_ensure_loggedin();
	}

	$method = get_str("method");

	if (! $method){
		error_404();
	}

	if (! isset($GLOBALS['cfg']['api']['methods'][$method])){
		error_404();
	}

	$details = $GLOBALS['cfg']['api']['methods'][$method];

	if (! api_methods_can_view_method($details, $GLOBALS['cfg']['user']['id'])){
		error_404();
	}

	$rsp = api_spec_utils_example_for_method($method);

	if ($rsp['ok']){
		$details['example_response'] = $rsp['example'];
	}

	$extras = $GLOBALS['cfg']['api_methods']['extras'];
	$GLOBALS['smarty']->assign("extras", $extras);

	$GLOBALS['smarty']->assign("method", $method);
	$GLOBALS['smarty']->assign_by_ref("details", $details);

	$logged_out_token = api_oauth2_access_tokens_fetch_api_explorer_token();
	$GLOBALS['smarty']->assign_by_ref("logged_out_token", $logged_out_token);

	if ($user = $GLOBALS['cfg']['user']){
		$read_only_token = api_oauth2_access_tokens_fetch_api_explorer_token($user);
		$GLOBALS['smarty']->assign_by_ref("read_only_token", $read_only_token);
	}

	$GLOBALS['smarty']->display("page_api_method_explore.txt");
	exit();

?>
