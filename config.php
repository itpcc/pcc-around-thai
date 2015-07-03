<?php
	//declare(encoding='UTF-8');
	//error_reporting(E_ALL);
	error_reporting(E_ERROR | E_PARSE);
	ini_set('display_errors', 1);
	$news = array();
	$error = array();

	$verifiedSchool = array(
		'pcccr'		=> 'เชียงราย',
		'pccloei'	=> 'เลย',
		'pccm'		=> 'มุกดาหาร',
		'pccp'		=> 'ปทุมธานี',
		'pccphet'	=> 'เพชรบุรี',
		'pccpl'		=> 'พิษณุโลก',
		'pccst'		=> 'สตูล',
		'pcctrg'	=> 'ตรัง',
		'pccchon'	=> 'ชลบุรี',
		'pccl'		=> 'ลพบุรี',
		'pccbr'		=> 'บุรีรัมย์'
		);
	$config = array(
		'cache'	=> array(
			'school'	=> 60*60*12
			)
		);