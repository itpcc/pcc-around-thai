<?php
	require_once dirname(__FILE__).'/config.php';
	require_once dirname(__FILE__).'/php_fast_cache.php';
	$cacheName = 'news-pccl';$dataURL = "http://www.pccl.ac.th/index2.php?usid=20100003&dummy=".rand();
	phpFastCache::$storage = "auto";
	// ready ?
	// check in case first
	$news['pccl'] = phpFastCache::get($cacheName);
	//$news['pccl'] = null;
	if($news['pccl'] == null) {

		$mustDecode = true;
		
		//$dataURL = "pccl.txt";
		
		$news['pccl'] =  array();

		$rawData = file_get_contents($dataURL);
		if(empty($rawData)){
			$error['pccl'] = 404;
		}else{
			if($mustDecode) $rawData = iconv('windows-874', "UTF-8", $rawData);
			$isFound = preg_match('#<img height=\"32\" width=\"32\" src=\"img\/news.png\" style=\"float: left;\" \/><span style=\"font-size: small;\">ข่าวสาร</span>(.*?)<\/table?>#s', $rawData, $matches);
			if($isFound){
				//preg_match_all('#background=\"images\/cal1.png\"(.*?)valign=\"top\"#s', $matches[1][0], $newsAll);
				$newsAll = explode('<tr style="background-color: #ffffff;">', $matches[1]); unset($newsAll[0]);
				$cnt = count($newsAll);
				for($i=1; $i<=$cnt; $i+=2){
					$newsAll[$i] .= $newsAll[$i+1];
					unset($newsAll[$i+1]);
				}

				foreach (array_values($newsAll) as $i => $newsItem){
					$news['pccl'][$i] = array(
						'title'	=>	'',
						'thumb'	=>	'',
						'content'	=>	'',
						'link'	=>	'',
						'pub_date'	=>	''
					);
					
					//image thumb
					if(preg_match("/<img .*?(?=src)src=\"([^\"]+)\"/si", $newsItem, $tempImage))
						$news['pccl'][$i]['thumb'] = dirname($dataURL)."/{$tempImage[1]}";
					//title and content
					if(preg_match('#<\/span?><\/span?><\/span?>(.*?)<a#s', $newsItem, $tempContent)){
						$news['pccl'][$i]['content'] = str_replace(array('/wbr>'), '', trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($tempContent[1])))));
						$news['pccl'][$i]['title'] = function_exists('mb_substr')?mb_substr($news['pccl'][$i]['content'], 0, 100, 'UTF-8'):substr($news['pccl'][$i]['content'], 0, 200);
						if($news['pccl'][$i]['title']!==$news['pccl'][$i]['content']) 
							$news['pccl'][$i]['title'] .= '...';
					}
					//pub_date
					$news['pccl'][$i]['pub_date'] = time();
					//link
					if(preg_match("/<a .*?(?=href)href=\"([^\"]+)\"/si", $newsItem, $templink))
						$news['pccl'][$i]['link'] = dirname($dataURL)."/{$templink[1]}";

					//clear news that have no MTFK content olo
					if(empty($news['pccl'][$i]['content'])) unset($news['pccl'][$i]);
				}
				phpFastCache::set($cacheName,json_encode($news['pccl']),$config['cache']['school']);
			}else{
				$error['pccl'] = 500;
			}
		}
	}else{
		$news['pccl'] = json_decode($news['pccl'], true);
	}