<?php
	require_once dirname(__FILE__).'/php_fast_cache.php';
	var_dump(phpFastCache::cleanup());
	var_dump(phpFastCache::get('news-pcccr'));