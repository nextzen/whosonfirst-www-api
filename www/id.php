<?php

	include("include/init.php");
	loadlib("whosonfirst_places");
	loadlib("whosonfirst_placetypes");

	if (! get_isset("id")){
		header("location: {$GLOBALS['cfg']['abs_root_url']}");
		exit();
	}

	$id = get_int64("id");

	$place = whosonfirst_places_get_by_id($id);

	if (! $place){
		error_404();
	}

	if (get_isset("nearby")){
		$url = whosonfirst_places_nearby_url_for_place($place);
		header("location: {$url}");
		exit();
	}

	$parent_id = $place["wof:parent_id"];

	if ($parent_id > -1){
		$parent = whosonfirst_places_get_by_id($parent_id);
		$place["wof:parent"] = $parent;
	}

	else {

		$pt = $place["wof:placetype"];
		$hiers = $place["wof:hierarchy"];

		$parent_ids = array();
		$parent_key = null;

		foreach (whosonfirst_placetypes_parents($pt) as $ppt){

			$k = "{$ppt}_id";

			if (isset($hiers[0][$k])){
				$parent_key = $k;
				break;
			}
		}

		foreach ($hiers as $hier){
			$parent_ids[] = $hier[$parent_key];
		}

		$rsp = whosonfirst_places_get_by_id_multi($parent_ids);

		if ($rsp['ok']){
			$place["wof:parents"] = $rsp["rows"];
		}
	}

	if ($place['wof:concordances'] &&
	    $place['wof:concordances']['wk:page']){
		$GLOBALS['smarty']->assign('wikipedia_page', $place['wof:concordances']['wk:page']);
	}

	$search_url = whosonfirst_places_search_referer_url($query, $filters, $args);
	$GLOBALS['smarty']->assign("search_url", $search_url);

	$GLOBALS['smarty']->assign_by_ref("place", $place);
	$GLOBALS['smarty']->assign_by_ref("mz_hours", $place['mz:hours']);

	$links = array();
	$link_properties = array(
		'addr:website' => 'Website',
		'addr:twitter' => 'Twitter',
		'addr:facebook' => 'Facebook',
		'addr:instagram' => 'Instagram',
		'addr:youtube' => 'YouTube',
		'addr:github' => 'GitHub'
	);
	foreach ($link_properties as $prop => $text){
		if ($place[$prop]){
			$url = $place[$prop];
			$links[$url] = $text;
		}
	}
	if (isset($place['wof:concordances']['4sq:id'])) {
		$id = $place['wof:concordances']['4sq:id'];
		$url = "https://foursquare.com/v/$id";
		$links[$url] = 'FourSquare';
	}
	$GLOBALS['smarty']->assign_by_ref("links", $links);

	if (get_bool('old')){
		$GLOBALS['smarty']->display("page_id_old.txt");
	} else {
		$GLOBALS['smarty']->display("page_id.txt");
	}

	exit();
?>
