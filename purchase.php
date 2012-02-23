<?php
//Enter your APP ID below
define('APP_ID', '444');
define('APP_SECRET', '88be0fc9a129121d6743f6d49d2d30a5');

//1.include
include 'php-sdk/src/fun.php';

//2.基本設定
$config = array(
	'appId'  	=> APP_ID,                                 //your app id
	'secret' 	=> APP_SECRET,
	'redirect_uri'  => 'http://10.0.2.106/kevyu/api/currency/purchase.php',
	'debugging'	=> false
);
//3.實體化
$fun = new FUN($config);

//4.取得並夾帶access token
$session = $fun->getSession();      
if($session){
//	$fun->api('v1/me/user');
	define('ACCESS_TOKEN', $fun->getAccessToken());
	$fun->api('v1/me/user');
} else {
	die('<a href="'.$fun->getLoginUrl($config['redirect_uri']).'">login F8D</a>');
}

?>
<html>
	<head>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="http://api.fun.wayi.com.tw/assets/jqplugin/f8d.js?v=20120110001"></script>

</head>
<body>
<h1>Wayi金流 儲值遊戲幣範例</h1>
<table border=1>
	<caption>儲值金額</caption>
	<th>遊戲幣</th><th>點數</th><th></th>
	<tr>
		<td style="text-align:right;">1 <img src="gold.gif" /></td>
		<td >2 WGS points (測WGS)</td>
		<td>
<a onclick="placeOrder('ITEM0001','1遊戲幣',1 ,'1遊戲幣 = 2 WGS', 'http://10.0.2.106/kevyu/api/currency/gold.gif',''); return false;" type="button" name="fun_share" class="fun_share_button">Pay with F8D</a>
	</tr>
	<tr>
		<td style="text-align:right;">1000 <img src="gold.gif" /></td>
		<td>1000 WGS points (測餘額不足)</td>
		<td>
<a onclick="placeOrder('ITEM0002','1000遊戲幣',1000, '1 遊戲幣 = 1 WGS', 'http://10.0.2.106/kevyu/api/currency/gold.gif',''); return false;" type="button" name="fun_share" class="fun_share_button">Pay with F8D</a>
	</tr>

</table>
<link rel="stylesheet" type="text/css" href="http://api.fun.wayi.com.tw/assets/socialplugin/css/fun_share.css">
  </form>
<hr>
<div id="output"></div>

<script type="text/javascript">
$(function(){
	$('body').F8D.init({appid:"<?php echo APP_ID; ?>", access_token:"<?php echo ACCESS_TOKEN;?>"});
});	
//place an order
function placeOrder(itemid,title, price) {
	// Only send param data for sample. These parameters should be set
	// in the callback.
	var order_info = {
		itemid: itemid
	};

	// calling the API ...
	var obj = {
		method: 'pay',
			order_info: order_info,
			purchase_type: 'wgs'
	};


	$('body').F8D.ui(obj, callback);
}

function callback(data){
	if (data['orderid']) {
		writeback("Transaction Completed! </br></br>"
			+ "Data returned from F8D: </br>"
			+ "<b>Order ID: </b>" + data['orderid'] + "</br>"
			+ "<b>Status: </b>" + data['status'] + "</br>"
			+ "<b>All Info: </b>" + JSON.stringify(data));
	} else if (data['error_code']) {
		writeback("Transaction Failed! </br></br>"
			+ "Error message returned from F8D:</br>"
			+ data['error_message']);
	} else {
		writeback("Transaction failed! </br>"
			+ JSON.stringify(data)
		);
	}
}

function writeback(str) {
	document.getElementById('output').innerHTML=str;
}

</script>
<hr>
</body>
</html>
