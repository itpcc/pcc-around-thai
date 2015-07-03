<?php
	require_once dirname(__FILE__).'/config.php';
	require_once dirname(__FILE__).'/php_fast_cache.php';
	$cacheName = 'news-pccchon';$dataURL = "http://www.pccchon.ac.th/index1.html?dummy=".rand();
	phpFastCache::$storage = "auto";
	// ready ?
	// check in case first
	$news['pccchon'] = phpFastCache::get($cacheName);
	//$news['pccchon'] = null;
	if($news['pccchon'] == null) {
		$mustDecode = false;
		
		//$dataURL = "pccchon.txt";
		
		$news['pccchon'] =  array();

		$rawData = file_get_contents($dataURL);
		//$dataURL = "http://www.pccchon.ac.th/index1.html?dummy=".rand();
		if(empty($rawData)){
			$error['pccchon'] = 404;
		}else{
			if($mustDecode) $rawData = iconv('TIS-620', "UTF-8", $rawData);
			$isFound = preg_match_all('#<table width=\"658\"(?:\s+[^>]+)?>(.*?)<img src=\"images\/Apps.png\"#s', $rawData, $matches);
			if($isFound){
				preg_match_all('#background=\"images\/cal1.png\"(.*?)valign=\"top\"#s', $matches[1][0], $newsAll);
				foreach ($newsAll[1] as $i => $newsItem){
					//echo $newsItem,PHP_EOL,'---------------------------------',PHP_EOL;
					$news['pccchon'][$i] = array(
						'title'	=>	'',
						'thumb'	=>	'',
						'content'	=>	'',
						'link'	=>	'',
						'pub_date'	=>	''
						);
					//image thumb
					if(preg_match("/<img .*?(?=src)src=\"([^\"]+)\"/si", $newsItem, $tempImage))
						$news['pccchon'][$i]['thumb'] = dirname($dataURL)."/{$tempImage[1]}";
					//title and content
					if(preg_match_all('#<a(?:\s+[^>]+)?>(.*?)<\/a#s', $newsItem, $tempContent) >= 2){
						$news['pccchon'][$i]['content'] = $news['pccchon'][$i]['title'] = trim(preg_replace('/\s+/', ' ', strip_tags(array_pop($tempContent[1]))));
						$news['pccchon'][$i]['content'] = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1'.dirname($dataURL).'/$2$3', $html);
					}
					//pub_date
					if(preg_match('#<span class=\"style17\"?>(.*?)<\/span#s', $newsItem, $tempPubDate)){
						$arrPubdate = explode('/', $tempPubDate[1]);
						$arrPubdate[2]	= intval($arrPubdate[2])+1957;
						//$news['pccchon'][$i]['pub_date'] = strtotime(sprintf("%04d/%02d/%02d", $arrPubdate[2], $arrPubdate[1], $arrPubdate[0]));
					}
					//link
					if(preg_match("/<a .*?(?=href)href=\"([^\"]+)\"/si", $newsItem, $templink))
						$news['pccchon'][$i]['link'] = dirname($dataURL)."/{$templink[1]}";

				}
				phpFastCache::set($cacheName,json_encode($news['pccchon']),$config['cache']['school']);
			}else{
				$error['pccchon'] = 500;
			}
		}
	}else{
		$news['pccchon'] = json_decode($news['pccchon'], true);
	}