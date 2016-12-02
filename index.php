<?php
set_time_limit(-1);

require 'phpQuery/phpQuery.php';
require 'helper_functions.php';
	
const SITE = 'http://carnomer.ru';	
file_put_contents('data.csv', '');
touch('checker.dd');
parse(SITE);

function parse($url){
	if(!file_exists('checker.dd')){
		s('вызвана остановка',1);
		exit;
	}
	s($url);
	$doc = file_get_contents($url);
	$doc = phpQuery::newDocument($doc);
	$elements = pq('#page-content-wrap tr.js-link');
	foreach($elements as $elem){
		$number = getNum(pq('td:eq(0) img', $elem)->attr('alt'));
		$price = pq('.td-price .f_price', $elem)->text() . pq('.td-price .f_price2', $elem)->text() ;//. pq('.td-price .grz-hint', $elem)->text();
		$name = pq('.td-seller a', $elem)->text();
		$phone = getPhone(pq('.td-seller a', $elem)->attr('data-id'));
		$city = pq('.td-region', $elem)->text();
		$data = array_map('trim',compact('number','price','name','phone','city'));
		saveData($data);
	}

	$next = pq('.pagination .next a', $doc)->attr('href');
	$doc->unloadDocument();
	if($next){
	parse(SITE . $next);
	}
	
	
	
}




function getNum($text){
	
	$num = preg_replace('~Продажа номера (.+) \(.*~','$1',$text);
	
	$num;
	
	return $num;
}


function getPhone($id){
	$context = stream_context_create([
		'http'=>[
			'header'=>"X-Requested-With: XMLHttpRequest\n",
		],
	]);
	$card = file_get_contents(SITE . '/ajax/get-user-info?user_id=' . $id, null, $context);
	
	$doc = phpQuery::newDocument($card);
	$id = pq('.get-phone')->attr('data-id');
	$doc->unloadDocument();
	
	$phone = file_get_contents(SITE . '/ajax/get-phone?user_id=' . $id, null, $context);
	
	$phone = strip_tags($phone);
	
	return $phone;
	
}

function saveData($data){
	
	$fd = fopen('data.csv', 'a+');
	$data = array_map(function($i){
		return iconv('utf-8','cp1251',$i);
	}, $data);
	fputcsv($fd, $data, ';');
	
	fclose($fd);
		
}