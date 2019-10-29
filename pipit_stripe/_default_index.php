<?php

	# include the API and classes
	include(__DIR__.'/../../../core/inc/api.php');
	
	foreach($classes as $class)	{
		include($class);
	}

	$API  = new PerchAPI(1.0, 'pipit_stripe');
	$Lang = $API->get('Lang');
	$HTML = $API->get('HTML');
	$Paging = $API->get('Paging');
	$Template = $API->get('Template');

	
	# Do anything you want to do before output is started
	$Perch->page_title = $Lang->get($title);
	include('modes/_subnav.php');
	include('modes/'.$mode.'.pre.php');

	# Top layout
	include(PERCH_CORE . '/inc/top.php');

	# Display your page
	include('modes/'.$mode.'.post.php');

	# Bottom layout
	include(PERCH_CORE . '/inc/btm.php');
