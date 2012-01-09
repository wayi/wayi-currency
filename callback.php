<?php
// Copyright 2004-Present Facebook. All Rights Reserved.

/**
 * You should reference http://developers.facebook.com/docs/credits/ as you
 * familiarize yourself with callback.php. In particular, read all the steps
 * under "Credits Tutorial" and "Credits Callback".
 *
 * Your application needs the following inputs and outputs
 *
 * @param int order_id
 * @param string status
 * @param string method
 * @param array order_details (JSON-encoded)
 *
 * @return array A JSON-encoded array with order_id, next_state (optional: error code, comments)
 */

// Enter your app information below
$app_secret = '33f2c72705bfaabbef82b142a489412c';

// Prepare the return data array
$data = array('content' => array());

// Parse the signed_request to verify it's from Facebook
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

	// Per the credits api documentation, you should pass in an item 
	// reference and then query your internal DB for the proper 
	// information. Then set the item information here to be 
	// returned to facebook then shown to the user for confirmation.
	$items = array(
		'ITEM0001'	=> array(
			'itemid'	=> 'ITEM0001',
			'title' 	=> '1遊戲幣(測WGS)',
			'price' 	=> 2,
			'description' 	=> '1遊戲幣 = 2 WGS',
			'image_url' 	=> 'http://10.0.2.106/kevyu/api/currency/gold.gif',
			'product_url' 	=> 'http://www.facebook.com/images/gifts/21.png',
		),
		'ITEM0002'	=> array(
			'itemid'	=> 'ITEM0002',
			'title' 	=> '1000遊戲幣(測餘額不足)',
			'price' 	=>  1000,
			'description' 	=> '1遊戲幣 = 1 WGS',
			'image_url' 	=> 'http://10.0.2.106/kevyu/api/currency/gold.gif',
			'product_url' 	=> 'http://www.facebook.com/images/gifts/21.png',
		)

	);

	$itemid = $order_info['itemid'];
	if(!isset($items[$itemid])){
		die(make_error_report(sprintf ('item[%s] is not existed',$itemid )));
	}

	// Put the associate array of item details in an array, and return in the
	// 'content' portion of the callback payload.
	$data['content'] = $items[$itemid];
}

// Required by api_fetch_response()
$data['method'] = $func;

// Send data back
echo json_encode($data);

// You can find the following functions and more details
// on http://developers.facebook.com/docs/authentication/canvas.
function parse_signed_request($signed_request, $app_secret) {
	list($encoded_sig, $payload) = explode('.', $signed_request, 2);
	//
	// Decode the data
	$sig = base64_url_decode($encoded_sig);
	$data = json_decode(base64_url_decode($payload), true);

	if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
		die('1');
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
	//return base64_decode(strtr($input, '-_', '+/'));
	return base64_decode($input);
}

function make_error_report($message, $code = 500){
	return json_encode(array(
		'error' => array(
			'code' 	=> $code,
			'msg' 	=> $message
		)
	));
}
