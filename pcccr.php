<?php
	require_once dirname(__FILE__).'/config.php';
	require_once dirname(__FILE__).'/php_fast_cache.php';
	$cacheName = 'news-pcccr';
	$dataURL = "http://182.93.222.55/pcccr/enews/?dummy=".rand();
	phpFastCache::$storage = "auto";
	// ready ?
	// check in case first
	$news['pcccr'] = phpFastCache::get($cacheName);
	//$news['pcccr'] = null;
	if($news['pcccr'] == null) {

		$mustDecode = false;
		
		//$dataURL = "pcccr.txt";
		$news['pcccr'] =  array();
		
		$rawData = file_get_contents($dataURL);
		if(empty($rawData)){
			$error['pcccr'] = 404;
		}else{
			if($mustDecode) $rawData = iconv('windows-874', "UTF-8", $rawData);
			$isFound = preg_match_all("#<tr[^']*?bgcolor=?>(.*?)</tr>#s", $rawData, $matches);
			if($isFound){
				foreach ($matches[1] as $i => $newsItem) {					
					//extract column
					if(preg_match_all('#<td(?:\s+[^>]+)?>(.*?)</td>#s', $newsItem, $column)>=2){
						$news['pcccr'][$i] = array(
							'title'	=>	'',
							'thumb'	=>	'',
							'content'	=>	'',
							'link'	=>	'',
							'pub_date'	=>	''
						);
						//--left column
						$dom = new DOMDocument('1.0', 'UTF-8');
						$dom->loadHTML($column[1][0]);
						//get link
						$news['pcccr'][$i]['link'] = dirname($dataURL).'/'.$dom->getElementsByTagName('a')->item(0)->getAttribute('href');
						//get thumb
						$news['pcccr'][$i]['thumb'] = dirname($dataURL).'/'.$dom->getElementsByTagName('img')->item(0)->getAttribute('src');
						//clear memory
						$dom = null; unset($dom); gc_collect_cycles(); //sweep the floor
						//--right column
						if(preg_match('#<b(?:\s+[^>]+)?>(.*?)</b>#s', $column[1][1], $tempPubDate)){
							$tmpDate = !empty($tempPubDate[1])?(strpos($tempPubDate[1], '<![CDATA[')!==false?substr($tempPubDate[1], 9, strlen($tempPubDate[1])-12):$tempPubDate[1]):'';
							//title
							$news['pcccr'][$i]['title'] = $tmpDate;
							//pub_date
							$tmpDate = str_replace(
								array(
									'มกราคม',
									'กุมภาพันธ์',
									'มีนาคม',
									'เมษายน',
									'พฤษภาคม',
									'มิถุนายน',
									'กรกฎาคม',
									'สิงหาคม',
									'กันยายน',
									'ตุลาคม',
									'พฤศจิกายน',
									'ธันวาคม'
									),
								array(
									'jan',
									'feb',
									'mar',
									'apr',
									'may',
									'jun',
									'jul',
									'aug',
									'sep',
									'oct',
									'nov',
									'dec'
									),
								substr(
									str_replace(
										array(
											'อาทิตย์',
											'จันทร์',
											'อังคาร',
											'พุธ',
											'พฤหัสบดี',
											'ศุกร์',
											'เสาร์'
											), 
										'', 
										$tmpDate
									),
								34)
							);
							$tmpDate = explode(' ', $tmpDate); $tmpDate[2] = intval($tmpDate[2]) - 543;
							$news['pcccr'][$i]['pub_date'] = strtotime(implode(' ', $tmpDate));
						}
						//content
						if(preg_match('#<font?>(.*?)</font>#s', $newsItem, $tempContent)){
							$news['pcccr'][$i]['content'] = !empty($tempContent[1])?(strpos($tempContent[1], '<![CDATA[')!==false?substr($tempContent[1], 9, strlen($tempContent[1])-12):$tempContent[1]):'';
						}
					}					
				}
				phpFastCache::set($cacheName,json_encode($news['pcccr']),$config['cache']['school']);
			}else{
				$error['pcccr'] = 500;
			}
		}
	}else{
		$news['pcccr'] = json_decode($news['pcccr'], true);
	}