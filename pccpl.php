<?php
	require_once dirname(__FILE__).'/config.php';
	require_once dirname(__FILE__).'/php_fast_cache.php';
	$cacheName = 'news-pccpl';$dataURL = "http://www.pccpl.ac.th/pccpl2012/index.php/en/?format=feed&type=rss&dummy=".rand();
	phpFastCache::$storage = "auto";
	// ready ?
	// check in case first
	$news['pccpl'] = phpFastCache::get($cacheName);
	if($news['pccpl'] == null) {

		$mustDecode = false;
		
		$news['pccpl'] =  array();

		$rawData = file_get_contents($dataURL);
		if(empty($rawData)){
			$error['pccpl'] = 404;
		}else{
			if($mustDecode) $rawData = iconv('TIS-620', "UTF-8", $rawData);
			$isFound = preg_match_all('#<item(?:\s+[^>]+)?>(.*?)</item>#s', $rawData, $matches);
			if($isFound){
				foreach ($matches[1] as $i => $newsItem) {
					$news['pccpl'][$i] = array(
						'title'	=>	'',
						'thumb'	=>	'',
						'content'	=>	'',
						'link'	=>	'',
						'pub_date'	=>	''
					);
					if(preg_match('#<title(?:\s+[^>]+)?>(.*?)</title>#s', $newsItem, $tempTitle))
						$news['pccpl'][$i]['title'] = !empty($tempTitle[1])?(strpos($tempTitle[1], '<![CDATA[')!==false?substr($tempTitle[1], 9, strlen($tempTitle[1])-12):$tempTitle[1]):'';

					if(preg_match('#<link(?:\s+[^>]+)?>(.*?)</link>#s', $newsItem, $tempLink))
						$news['pccpl'][$i]['link'] = !empty($tempLink[1])?(strpos($tempLink[1], '<![CDATA[')!==false?substr($tempLink[1], 9, strlen($tempLink[1])-12):$tempLink[1]):'';

					if(preg_match('#<pubDate(?:\s+[^>]+)?>(.*?)</pubDate>#s', $newsItem, $tempPubDate)){
						$news['pccpl'][$i]['pub_date'] = !empty($tempPubDate[1])?(strpos($tempPubDate[1], '<![CDATA[')!==false?substr($tempPubDate[1], 9, strlen($tempPubDate[1])-12):$tempPubDate[1]):'';
						$news['pccpl'][$i]['pub_date'] = strtotime($news['pccpl'][$i]['pub_date']);
					}
					

					if(preg_match('#<description(?:\s+[^>]+)?>(.*?)</description>#s', $newsItem, $tempContent)){
						$news['pccpl'][$i]['content'] = !empty($tempContent[1])?(strpos($tempContent[1], '<![CDATA[')!==false?substr($tempContent[1], 9, strlen($tempContent[1])-12):$tempContent[1]):'';
						//$news['pccpl'][$i]['content'] = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1'.dirname($dataURL).'/$2$3', $html);
					}
					if(preg_match("/<img .*?(?=src)src=\"([^\"]+)\"/si", $news['pccpl'][$i]['content'], $tempImage))
						$news['pccpl'][$i]['thumb'] = !empty($tempImage[1])?$tempImage[1]:'';
				}
				phpFastCache::set($cacheName,json_encode($news['pccpl']),$config['cache']['school']);
			}else{
				$error['pccl'] = 500;
			}
		}
	}else{
		$news['pccpl'] = json_decode($news['pccpl'], true);
	}