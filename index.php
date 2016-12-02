<?php


require 'phpQuery/phpQuery.php';
require 'helper_functions.php';
	
const SITE = 'http://carnomer.ru';	


parse(SITE);

function parse($url){
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
		x($data);
	}
	
	
	// saveData($data);
	
	
	$next = pq('.pagination .next a')->attr('href');
	$doc->unloadDocument();
	if($next){
		echo $next . '<br>';
		// parse(SITE . $next);
	}
}




function getNum($text){
	
	$num = preg_replace('~Продажа номера (.+) \(.*~','$1',$text);
	echo $num;
	
	return $num;
}


function getPhone($id){
	$context = stream_context_create([
		'http'=>[
			'header'=>"X-Requested-With: XMLHttpRequest\n",
		],
	]);
	$card = file_get_contents(SITE . '/ajax/get-user-info?user_id=' . $id,null,$context);
	j($card);
	$phone = '';
	
	return $phone;
	
}