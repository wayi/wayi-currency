<?php
//Enter your APP ID below
define('APP_ID', YOUR_APP_ID);
define('APP_SECRET', YOUR_APP_SECRET);
define('REDIRECT_URI', YOUR_REDIRECT_URI);

//1.include
include 'php-sdk/src/fun.php';

//2.基本設定
$config = array(
	'appId'  	=> APP_ID,		//your app id
	'secret' 	=> APP_SECRET,		//your app secret
	'redirect_uri'  => REDIRECT_URI,	//you game url
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
	<script type="text/javascript" src="http://api.fun.wayi.com.tw/assets/jqplugin/f8d.js?v=20120110002"></script>
</head>
<body>
<h1>Wayi金流 儲值遊戲幣範例</h1>
直接從玩家帳號扣除WGS點數，進而購買商品。 
<h4>1.<a target="_blank" href="http://developers.fun.wayi.com.tw/guides/currency_sdk#商品模式(廠商實作介面)">商品模式</a></h4>
<table border=1>
	<caption>儲值金額</caption>
	<th>遊戲幣</th><th>點數</th><th></th>
	<tr>
		<td style="text-align:right;">1 <img src="gold.gif" /></td>
		<td >2 WGS points (測WGS)</td>
		<td>
<a onclick="place_order('ITEM0001'); return false;" type="button" name="fun_share" class="fun_share_button">Pay with F8D</a>
	</tr>
	<tr>
		<td style="text-align:right;">1000 <img src="gold.gif" /></td>
		<td>1000 WGS points (測餘額不足)</td>
		<td>
<a onclick="place_order('ITEM0002'); return false;" type="button" name="fun_share" class="fun_share_button">Pay with F8D</a>
	</tr>

</table>
<link rel="stylesheet" type="text/css" href="http://api.fun.wayi.com.tw/assets/socialplugin/css/fun_share.css">
  </form>
<br/>
<h4>2.<a target="_blank" href="http://developers.fun.wayi.com.tw/guides/currency_sdk#儲值模式(廠商實作介面)">儲值模式</a></h4>
儲值模式提供直接儲值遊戲幣功能。User選擇儲值管道後，直接選擇兌換額度，並全部轉成遊戲幣，即不依附商品。<br/>
<input value="儲值模式" type="button" onclick="javascript:gamecash_mode();">
<hr>
Reply
<div id="output" style="border:1px solid;background-color:#FFFFCC;height:300px;"></div>
<script type="text/javascript">
$(function(){
	//$('body').F8D.init({appid:"<?php echo APP_ID; ?>", access_token:"<?php echo ACCESS_TOKEN;?>"});
	$('body').F8D.init({appid:"<?php echo APP_ID; ?>", access_token:"<?php echo ACCESS_TOKEN;?>"});


	//resize
	 $(this).fun.iframe.setAutoResize();
});	

		    
//place an order
function place_order(itemid) {
	// Only send param data for sample. These parameters should be set
	// in the callback.
	var order_info = {
		itemid: itemid,
		test: 'testdata1'
	};

	// calling the API ...
	var obj = {
		method: 'payment_product_mode',
		order_info: order_info
	};


	$('body').F8D.ui(obj, callback);
}

function gamecash_mode(){
	var order_info = {
		test: 'testdata2'
	};

	// calling the API ...
	var obj = {
		method: 'payment_gamecash_mode',
		order_info: order_info
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
	} else if (data.error_code) {
		writeback("Transaction Failed! </br></br>"
			+ "Error message returned from F8D:</br>"
			+ "<b>Error code: </b>" + data['error_code'] + '<br/>'
			+ "<b>Error Message: </b>" + data['error_message'] + '<br/>'
			+ "<b>All Info: </b>" + JSON.stringify(data));
	} else {
		writeback("Transaction failed! </br>"
			+ JSON.stringify(data.error_message)
		);
	}
}

function writeback(str) {
	document.getElementById('output').innerHTML=str;
}

</script>
<br/>
<a href="#top" onclick="$(this).fun.iframe.move2Top();">top</a>
</body>
</html>
