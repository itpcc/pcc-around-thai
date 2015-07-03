<?php
	require_once dirname(__FILE__).'/config.php';
	require_once dirname(__FILE__).'/php_fast_cache.php';
	$cacheName = 'news-pccm';$dataURL = "http://pccmukdahan.org/index.php?option=com_content&view=category&id=25&format=feed&type=rss&dummy=".rand();
	phpFastCache::$storage = "auto";
	// ready ?
	// check in case first
	$news['pccm'] = phpFastCache::get($cacheName);
	if($news['pccm'] == null) {

		$mustDecode = false;
		
		$news['pccm'] =  array();

		$rawData = file_get_contents($dataURL);
		if(empty($rawData)){
			$error['pccm'] = 404;
		}else{
			if($mustDecode) $rawData = iconv('TIS-620', "UTF-8", $rawData);
			$isFound = preg_match_all('#<item(?:\s+[^>]+)?>(.*?)</item>#s', $rawData, $matches);
			if($isFound){
				foreach ($matches[1] as $i => $newsItem) {
					$news['pccm'][$i] = array(
						'title'	=>	'',
						'thumb'	=>	'',
						'content'	=>	'',
						'link'	=>	'',
						'pub_date'	=>	''
					);

					//title
					if(preg_match('#<title(?:\s+[^>]+)?>(.*?)</title>#s', $newsItem, $tempTitle))
						$news['pccm'][$i]['title'] = !empty($tempTitle[1])?(strpos($tempTitle[1], '<![CDATA[')!==false?substr($tempTitle[1], 9, strlen($tempTitle[1])-12):$tempTitle[1]):'';

					if(preg_match('#<link(?:\s+[^>]+)?>(.*?)</link>#s', $newsItem, $tempLink))
						$news['pccm'][$i]['link'] = !empty($tempLink[1])?(strpos($tempLink[1], '<![CDATA[')!==false?substr($tempLink[1], 9, strlen($tempLink[1])-12):$tempLink[1]):'';

					if(preg_match('#<pubDate(?:\s+[^>]+)?>(.*?)</pubDate>#s', $newsItem, $tempPubDate)){
						$news['pccm'][$i]['pub_date'] = !empty($tempPubDate[1])?(strpos($tempPubDate[1], '<![CDATA[')!==false?substr($tempPubDate[1], 9, strlen($tempPubDate[1])-12):$tempPubDate[1]):'';
						$news['pccm'][$i]['pub_date'] = strtotime($news['pccm'][$i]['pub_date']);
					}						

					if(preg_match('#<description(?:\s+[^>]+)?>(.*?)</description>#s', $newsItem, $tempContent)){
						$news['pccm'][$i]['content'] = !empty($tempContent[1])?(strpos($tempContent[1], '<![CDATA[')!==false?substr($tempContent[1], 9, strlen($tempContent[1])-12):$tempContent[1]):'';
						//$news['pccm'][$i]['content'] = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1'.dirname($dataURL).'/$2$3', $html);
					}
					if(preg_match("/<img .*?(?=src)src=\"([^\"]+)\"/si", $news['pccm'][$i]['content'], $tempImage))
						$news['pccm'][$i]['thumb'] = !empty($tempImage[1])?$tempImage[1]:'';
				}
				phpFastCache::set($cacheName,json_encode($news['pccm']),$config['cache']['school']);
			}else{
				$error['pccm'] = 500;
			}
		}
	}else{
		$news['pccm'] = json_decode($news['pccm'], true);
	}