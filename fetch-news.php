<?php
	header('Content-Type: application/json');
	require_once dirname(__FILE__).'/config.php';
	require_once dirname(__FILE__).'/php_fast_cache.php';

	if(!isset($_GET['newsfrom']) OR !isset($verifiedSchool[$_GET['newsfrom']])) 
		die(json_encode(array("error"=>"not in verified school.")));
	

	$cacheName = "fetch-{$_GET['newsfrom']}";
	phpFastCache::$storage = "auto";
	// ready ?
	// check in case first
	$content = phpFastCache::get($cacheName);
	//$content = null;
	if($content == null) {
		include dirname(__FILE__).'/'.basename("{$_GET['newsfrom']}.php");
		if(isset($error[$_GET['newsfrom']]) OR empty($news[$_GET['newsfrom']])){
			die(json_encode(array("error"=>("Cannot fetch news from {$verifiedSchool[$_GET['newsfrom']]}.".empty($news[$_GET['newsfrom']])?'Y':$error[$_GET['newsfrom']]))));
		}else{
			$news[$_GET['newsfrom']]['data_url'] = dirname($dataURL);
			$content = json_encode($news[$_GET['newsfrom']]);			
			phpFastCache::set($cacheName,$content,$config['cache']['school']);
		}
	}
	//var_dump($news);
	echo $content;