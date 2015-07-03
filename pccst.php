<?php
	require_once dirname(__FILE__).'/config.php';
	require_once dirname(__FILE__).'/php_fast_cache.php';
	$cacheName = 'news-pccst';$dataURL = "http://www.pccst.ac.th/?dummy=".rand();
	phpFastCache::$storage = "auto";
	// ready ?
	// check in case first
	$news['pccst'] = phpFastCache::get($cacheName);
	if($news['pccst'] == null) {

		$mustDecode = true;
		
		//$dataURL = "pccst.txt";
		
		$news['pccst'] =  array();

		$rawData = file_get_contents($dataURL);
		//$dataURL = "http://www.pccst.ac.th/?dummy=".rand();
		if(empty($rawData)){
			$error['pccst'] = 404;
		}else{
			if($mustDecode) $rawData = iconv('TIS-620', "UTF-8", $rawData);
			$isFound = preg_match_all('#<td background=\"images\/body_news_body.png\" ?>(.*?)<a href=\"result_news_57.html\"#s', $rawData, $matches);
			if($isFound){
				if(preg_match_all('#<tr(?:\s+[^>]+)?>(.*?)</tr>#s', $matches[1][0], $newsAll)){
					$dom = new DOMDocument('1.0', 'UTF-8'); $i = 0;
					foreach ($newsAll[1] as $newsItem){
						//extract column
						preg_match_all('#<td(?:\s+[^>]+)?>(.*?)</td>#s', $newsItem, $column);
						//check that not a blank node
						if(!empty($column[1][0]) && $column[1][0]!=='&nbsp;'){
							$news['pccst'][$i] = array(
								'title'	=>	'',
								'thumb'	=>	'',
								'content'	=>	'',
								'link'	=>	'',
								'pub_date'	=>	''
							);
							//image thumb
							if(preg_match("/<img .*?(?=src)src=\"([^\"]+)\"/si", $column[1][0], $tempImage))
								$news['pccst'][$i]['thumb'] = dirname($dataURL)."/{$tempImage[1]}";
							//title and content
							if(preg_match('#<p(?:\s+[^>]+)?>(.*?)<a#s', $column[1][1], $tempContent)){
								$news['pccst'][$i]['content'] = $tempContent[1];
								$news['pccst'][$i]['title'] = function_exists('mb_substr')?mb_substr(strip_tags(nl2br($tempContent[1])), 0, 200, 'UTF-8'):substr(strip_tags(nl2br($tempContent[1])), 0, 200);
								if($news['pccst'][$i]['title']!==$news['pccst'][$i]['content']) 
									$news['pccst'][$i]['title'] .= '...';
							}

							//link
							if(preg_match("/<a .*?(?=href)href=\"([^\"]+)\"/si", $column[1][1], $tempLink))
								$news['pccst'][$i]['link'] = dirname($dataURL)."/{$tempLink[1]}";

							$news['pccst'][$i]['pub_date'] = time();
							$tempPubDate = @get_headers($news['pccst'][$i]['link']);
							if(!empty($tempPubDate)){
								foreach ($tempPubDate as $header) {
									if(strpos($header, 'Last-Modified:')!==false)
										$news['pccst'][$i]['pub_date'] = strtotime(substr($header, 15));
								}
							}
							$i++;
						}
					}
					phpFastCache::set($cacheName,json_encode($news['pccst']),$config['cache']['school']);
				}else{
					$error['pccst'] = 500;
				}
				
			}else{
				$error['pccst'] = 500;
			}
		}
	}else{
		$news['pccst'] = json_decode($news['pccst'], true);
	}