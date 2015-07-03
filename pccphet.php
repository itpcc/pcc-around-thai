<?php
	require_once dirname(__FILE__).'/config.php';
	require_once dirname(__FILE__).'/php_fast_cache.php';
	$cacheName = 'news-pccphet';$dataURL = "http://www.pccphet.ac.th/newpccphet/feed/?dummy=".rand();
	phpFastCache::$storage = "auto";
	// ready ?
	// check in case first
	$news['pccphet'] = phpFastCache::get($cacheName);
	if($news['pccphet'] == null) {

		$mustDecode = false;
		
		$news['pccphet'] =  array();

		$rawData = file_get_contents($dataURL);
		if(empty($rawData)){
			$error['pccphet'] = 404;
		}else{
			if($mustDecode) $rawData = iconv('TIS-620', "UTF-8", $rawData);
			$isFound = preg_match_all('#<item(?:\s+[^>]+)?>(.*?)</item>#s', $rawData, $matches);
			if($isFound){
				foreach ($matches[1] as $i => $newsItem) {
					$news['pccloei'][$i] = array(
						'title'	=>	'',
						'thumb'	=>	'',
						'content'	=>	'',
						'link'	=>	'',
						'pub_date'	=>	''
					);
					if(preg_match('#<title(?:\s+[^>]+)?>(.*?)</title>#s', $newsItem, $tempTitle))
						$news['pccphet'][$i]['title'] = !empty($tempTitle[1])?(strpos($tempTitle[1], '<![CDATA[')!==false?substr($tempTitle[1], 9, strlen($tempTitle[1])-12):$tempTitle[1]):'';

					if(preg_match('#<link(?:\s+[^>]+)?>(.*?)</link>#s', $newsItem, $tempLink))
						$news['pccphet'][$i]['link'] = !empty($tempLink[1])?(strpos($tempLink[1], '<![CDATA[')!==false?substr($tempLink[1], 9, strlen($tempLink[1])-12):$tempLink[1]):'';

					if(preg_match('#<pubDate(?:\s+[^>]+)?>(.*?)</pubDate>#s', $newsItem, $tempPubDate)){
						$news['pccphet'][$i]['pub_date'] = !empty($tempPubDate[1])?(strpos($tempPubDate[1], '<![CDATA[')!==false?substr($tempPubDate[1], 9, strlen($tempPubDate[1])-12):$tempPubDate[1]):'';
						$news['pccphet'][$i]['pub_date'] = strtotime($news['pccphet'][$i]['pub_date']);
					}

					if(preg_match('#<content:encoded(?:\s+[^>]+)?>(.*?)</content:encoded>#s', $newsItem, $tempContent)){
						$news['pccphet'][$i]['content'] = !empty($tempContent[1])?(strpos($tempContent[1], '<![CDATA[')!==false?substr($tempContent[1], 9, strlen($tempContent[1])-12):$tempContent[1]):'';
						//$news['pccphet'][$i]['content'] = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1'.dirname($dataURL).'/$2$3', $html);
					}
					if(preg_match("/<img .*?(?=src)src=\"([^\"]+)\"/si", $news['pccphet'][$i]['content'], $tempImage))
						$news['pccphet'][$i]['thumb'] = !empty($tempImage[1])?$tempImage[1]:'';
				}
				phpFastCache::set($cacheName,json_encode($news['pccphet']),$config['cache']['school']);
			}else{
				$error['pccphet'] = 500;
			}
		}
	}else{
		$news['pccphet'] = json_decode($news['pccphet'], true);
	}