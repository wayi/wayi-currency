<?php
//1.include
include '../componments/php-sdk/src/fun.php';

//2.基本設定
$config_411 = array(
	'appId'  	=> '411',                                 //your app id
	'secret' 	=> '33f2c72705bfaabbef82b142a489412c',
	'redirect_uri'  => 'http://10.0.2.106/kevyu/api/currency/purchase.php',
	'testing'	=> true,
	'keepCookie'	=> false
);
//3.實體化
$config = $config_411;
$fun = new FUN($config);

//4.取得並夾帶access token
$session = $fun->getSession();      
// Login or logout url will be needed depending on current user state.
if($session){
	$logoutUrl = $fun->getLogoutUrl();
//	echo sprintf('<a href="%s">logout</a>',$logoutUrl);
	$serial = $fun->getCurrencySerial();
} else {
	$loginUrl = $fun->getLoginUrl($config);
	//	echo sprintf('<a href="%s">login</a>',$loginUrl);
	$serial = '';
}


//Enter your APP ID below
define('APP_ID', '411');
define('ACCESS_TOKEN', $fun->getAccessToken());
define('SERIAL',$serial);
?>
<html>
	<head>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="f8d.js?v=12235"></script>

</head>
<body>
<h1>降龍之劍 儲值遊戲幣</h1>
<table border=1>
	<caption>儲值金額</caption>
	<th>遊戲幣</th><th>點數</th><th></th>
	<tr>
		<td style="text-align:right;">1 <img src="gold.gif" /></td>
		<td >2 WGS points (測WGS)</td>
		<td>
<a onclick="placeOrder('ITEM0001','1遊戲幣','1遊戲幣 = 2 WGS',100,'http://10.0.2.106/kevyu/api/currency/gold.gif',''); return false;" type="button" name="fun_share" class="fun_share_button">Pay with F8D</a>
	</tr>
	<tr>
		<td style="text-align:right;">1000 <img src="gold.gif" /></td>
		<td>1000 WGS points (測餘額不足)</td>
		<td>
<a onclick="placeOrder('ITEM0002','1遊戲幣','1 遊戲幣 = 1 WGS',1000,'http://10.0.2.106/kevyu/api/currency/gold.gif',''); return false;" type="button" name="fun_share" class="fun_share_button">Pay with F8D</a>
	</tr>

</table>
<link rel="stylesheet" type="text/css" href="http://api.fun.wayi.com.tw/assets/socialplugin/css/fun_share.css">
  </form>
<hr>
<div id="output"></div>

<script type="text/javascript">
$(function(){
	$('body').F8D.init({appid:"<?php echo APP_ID; ?>", access_token:"<?php echo ACCESS_TOKEN;?>", serial: "<?php echo SERIAL;?>"});
});	

function placeOrder(itemid,title, desc, price , img_url, product_url) {
	// Only send param data for sample. These parameters should be set
	// in the callback.
	var order_info = {
		itemid: itemid,
			"title":title,
			"description":desc,
			"price":price,
			"image_url":img_url,
			"product_url":product_url
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
			+ "<b>Status: </b>" + data['status']);
	} else if (data['error_code']) {
		writeback("Transaction Failed! </br></br>"
			+ "Error message returned from Facebook:</br>"
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
