<?php
	require_once dirname(__FILE__).'/config.php';
	require_once dirname(__FILE__).'/php_fast_cache.php';
	$cacheName = 'news-pccbr';
	$dataURL = "https://sites.google.com/site/pccbr2013/home/pr/posts.xml";
	phpFastCache::$storage = "auto";
	// ready ?
	// check in case first
	//$news['pccbr'] = phpFastCache::get($cacheName);
	$news['pccbr'] = null;
	if($news['pccbr'] == null) {

		$mustDecode = false;
		
		//$dataURL = "pccbr.txt";
		
		$news['pccbr'] =  array();

		$rawData = file_get_contents($dataURL);
		//$dataURL = "https://sites.google.com/site/pccbr2013/home/pr/posts.xml";
		if(empty($rawData)){
			$error['pccbr'] = 404;
		}else{
			if($mustDecode) $rawData = iconv('TIS-620', "UTF-8", $rawData);
			$isFound  = preg_match('#<feed(?:\s+[^>]+)?>(.*?)</feed>#s', $rawData, $matches);
			if($isFound){
				preg_match_all('#<entry(?:\s+[^>]+)?>(.*?)</entry>#s', $matches[1], $newsAll);
				foreach ($newsAll[1] as $i => $newsItem){
					//echo $newsItem,PHP_EOL,'---------------------------------',PHP_EOL;
					$news['pccbr'][$i] = array(
						'title'	=>	'',
						'thumb'	=>	'',
						'content'	=>	'',
						'link'	=>	'',
						'pub_date'	=>	''
					);
					//image thumb
					if(preg_match("/<img .*?(?=src)src=\"([^\"]+)\"/si", $newsItem, $tempImage))
						$news['pccbr'][$i]['thumb'] = $tempImage[1];
					//content
					if(preg_match('#<content(?:\s+[^>]+)?>(.*?)</content>#s', $newsItem, $tempContent)){
						$news['pccbr'][$i]['content'] = $tempContent[1];
					}
					//title
					if(preg_match('#<title?>(.*?)</title>#s', $newsItem, $tempTitle)){
						$news['pccbr'][$i]['title'] = $tempTitle[1];
					}
					//pub date
					if(preg_match('#<published(?:\s+[^>]+)?>(.*?)</published>#s', $newsItem, $tempPubDate)){
						$news['pccbr'][$i]['pub_date'] = strtotime($tempPubDate[1]);
					}
					//link
					if(preg_match('/<link(?:\s+[^>]+)type=\"text\/html\" .*?(?=href)href=\"([^\"]+)\"/si', $newsItem, $templink))
						$news['pccbr'][$i]['link'] = $templink[1];
				}
				phpFastCache::set($cacheName,json_encode($news['pccbr']),$config['cache']['school']);
			}else{
				$error['pccbr'] = 500;
			}
		}
	}else{
		$news['pccbr'] = json_decode($news['pccbr'], true);
	}