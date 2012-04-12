<?php
// Copyright 2011-Wayi. All Rights Reserved.

/**
 *	there are two methods for payment
 *	1.buy item
 *		related func name are
 *			- payments_get_itemds
 *			- payments_completed
 *	2.exchange gamecash
 *		related func name are
 *			-payments_get_gamecash
 *			-payments_gamecash_completed
 */

//1.Enter your app information below
$app_secret = '4c18b0e2186ec6280d06df970c0dbfa6';

//2.Prepare the return data array
$data = array('content' => array());

//3.Parse the signed_request to verify it's from f8d
$request = parse_signed_request($_REQUEST['signed_request'], $app_secret);

if ($request == null) {
	// Handle an unauthenticated request here
	die(make_error_report('unauthenticated'));
}

// Grab the payload
$payload = $request['credits'];

// Retrieve all params passed in
$func = $_REQUEST['method'];

if ($func == 'payments_completed') {
	$payload = json_decode(stripcslashes($payload),true);
	// Grab the order status
	$status = $payload['status'];
	// Write your apps logic here for validating and recording a
	// purchase here.
	//
	// Generally you will want to move states from `placed` -> `settled`
	// here, then grant the purchasing user's in-game item to them.
	if ($status == 'placed') {
		$next_state = 'settled';
		$data['content']['status'] = $next_state;
	}

	// Compose returning data array_change_key_case
	$orderid = $payload['orderid'];
	$data['content']['orderid'] = $orderid;

} else if ($func == 'payments_get_items') {
	// remove escape characters
	$order_info = json_decode(stripcslashes($payload),true);

	//item list
	$items = array(
		'ITEM0001' => array(
			'itemid' => 'ITEM0001',
			'title' => '1遊戲幣(測WGS)',
			'price' => 2,
			'gamecash' => 1,
			'description' => '2WGS = 1遊戲幣',
			'image_url' => 'http://10.0.2.106/kevyu/api/currency/gold.gif'
		),
		'ITEM0002' => array(
			'itemid' => 'ITEM0002',
			'title' => '1000遊戲幣(測餘額不足)',
			'price' => 1000,
			'gamecash' => 1000,
			'description' => '1WGS = 1遊戲幣',
			'image_url' => 'http://10.0.2.106/kevyu/api/currency/gold.gif'
		)

	);

	$itemid = $order_info['itemid'];
	if(!isset($items[$itemid])){
		die(make_error_report(sprintf ('item[%s] is not existed',$itemid )));
	}

	// Put the associate array of item details in an array, and return in the
	// 'content' portion of the callback payload.
	$data['content'] = $items[$itemid];
} else if ($func == 'payments_get_gamecash') {
	//some payment method can't save in wgs, so need to save all into game cash
	$credits = json_decode(stripcslashes($payload),true);
	$credit = (int)$credits['credits'];
	//pay with money
	$cash_info = array(
		'rate' => 2,
		'gamecash' => $credit * 2,
		'unit' => 'money',
		'unit_image' => 'http://10.0.2.106/kevyu/api/currency/gold.gif',
	);
	if(!isset($cash_info)){
		die(make_error_report(sprintf ('get ratio failed. content:%s',$payload )));
	}
	$data['content'] = $cash_info;
} else if ($func == 'payments_gamecash_completed') {
	$payload = json_decode(stripcslashes($payload),true);
	// Grab the order status
	$status = $payload['status'];
	// Write your apps logic here for validating and recording a
	// Generally you will want to move states from `placed` -> `settled`
	// here, then grant the purchasing user's in-game item to them.
	if ($status == 'placed') {
		$next_state = 'settled';
		$data['content']['status'] = $next_state;

		$makeup = $payload['makeup'];
	}

	// Compose returning data array_change_key_case
	//save
	$orderid = $payload['orderid'];
	$data['content']['orderid'] = $orderid;
}





// Required by api_fetch_response()
$data['method'] = $func;

// Send data back
echo json_encode($data);

function parse_signed_request($signed_request, $app_secret) {
	list($encoded_sig, $payload) = explode('.', $signed_request, 2);
	//
	// Decode the data
	$sig = base64_url_decode($encoded_sig);
	$data = json_decode(base64_url_decode($payload), true);

	if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
		error_log('Unknown algorithm. Expected HMAC-SHA256');
		return null;
	}

	// Check signature
	$expected_sig = hash_hmac('sha256', $payload, $app_secret, $raw = true);
	if ($sig !== $expected_sig) {
		error_log('Bad Signed JSON signature!');
		return null;
	}
	return $data;
}

function base64_url_decode($input) {
	return base64_decode($input);
}

function make_error_report($message, $code = 500){
	return json_encode(array(
		'error' => array(
			'code' => $code,
			'msg' => $message
		)
	));
}
