<?php
	require_once dirname(__FILE__).'/config.php';
	require_once dirname(__FILE__).'/php_fast_cache.php';
	$cacheName = 'news-pccloei';$dataURL = "http://www.pccloei.ac.th/pccloeiweb/web/showcontent.php?id=2&dummy=".rand();
	phpFastCache::$storage = "auto";
	// ready ?
	// check in case first
	$news['pccloei'] = phpFastCache::get($cacheName);
	//$news['pccloei'] = null;
	if($news['pccloei'] == null) {
		$mustDecode = false;
		
		
		$news['pccloei'] =  array();

		// $dataURL = 'pccloei.txt';
		$rawData = file_get_contents($dataURL);
		// $dataURL = "http://www.pccloei.ac.th/pccloeiweb/web/showcontent.php?id=2&dummy=".rand();
		if(empty($rawData)){
			$error['pccloei'] = 404;
		}else{
			if($mustDecode) $rawData = iconv('TIS-620', "UTF-8", $rawData);
			$isFound = preg_match('#<table width=\"800\"[^\']*?>(.*?)<\/table>#s', $rawData, $matches);
			if($isFound){
				if(preg_match_all("#<tr[^']*?>(.*?)</tr>#s", $matches[1], $newsAll)){
					foreach($newsAll[1] AS $i => $newsItem){
						$news['pccloei'][$i] = array(
							'title'	=>	'',
							'thumb'	=>	'',
							'content'	=>	'',
							'link'	=>	'',
							'pub_date'	=>	''
						);
						//thumb
						if(preg_match("/<img .*?(?=src)src='([^\"]+)' \/>/si", $newsItem, $tempImage))
							$news['pccloei'][$i]['thumb'] = dirname($dataURL)."/{$tempImage[1]}";
						//link
						if(preg_match("/<a .*?(?=href)href='(.*?)'>/si", $newsItem, $tempLink))
							$news['pccloei'][$i]['link'] = dirname($dataURL)."/{$tempLink[1]}";
						//title and content
						if(preg_match('#<a(?:\s+[^>]+)?>(.*?)</a>#s', $newsItem, $tempContent)){
							$tempContent[1] = trim($tempContent[1]);
							$news['pccloei'][$i]['content'] = $tempContent[1];
							$news['pccloei'][$i]['title'] = function_exists('mb_substr')?mb_substr(strip_tags(nl2br($tempContent[1])), 0, 100, 'UTF-8'):substr(strip_tags(nl2br($tempContent[1])), 0, 200);
							if($news['pccloei'][$i]['title']!==$news['pccloei'][$i]['content']) 
								$news['pccloei'][$i]['title'] .= '...';
							//$news['pccloei'][$i]['content'] = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1'.dirname($dataURL).'/$2$3', $html);
						}
						//pub_date
						if(preg_match('#<center(?:\s+[^>]+)?>(.*?)</center>#s', $newsItem, $tempPubDate)){
							$news['pccloei'][$i]['pub_date'] = strtotime(trim($tempPubDate[1]));
						}
					}
					phpFastCache::set($cacheName,json_encode($news['pccloei']),$config['cache']['school']);
				}
				else{
					$error['pccloei'] = 500;
				}
			}			
		}
	}else{
		$news['pccloei'] = json_decode($news['pccloei'], true);
	}