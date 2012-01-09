<?php
//Enter your APP ID below
define('APP_ID', '128211897279134');
?>
<html>
	<head>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="f8d.js"></script>

	<script type="text/javascript">
		$(function(){
			$(this).F8D.init({appId : '1'});
		});	
	</script>
</head>
<body>
  <form name ="place_order" id="order_form" action="#">
<h1>降龍之劍 購買道具</h1>
  Title:       <input type="text" name="title" value="降龍妹"
                id="title_el"> </br></br>
  Price:       <input type="text" name="price" value="99"
                id="price_el"> </br></br>
  Description: <input type="text" name="description" size="64"
                value="我來上...... " id="desc_el"> </br></br>
  Image URL:   <input type="text" name="image_url" size="64"
           value="http://images.gamme.com.tw/news/2011/11/2/qpqao6aYkZ6VqA-220x145.jpg"
                id="img_el"> </br></br>
  Product URL: <input type="text" name="product_url" size="64" value="http://gamemall.wayi.com.tw/shopping/boximg/aa02020129.jpg" id="product_el"> </br></br>
<link rel="stylesheet" type="text/css" href="http://api.fun.wayi.com.tw/assets/socialplugin/css/fun_share.css">
<a onclick="placeOrder(); return false;" type="button" name="fun_share" class="fun_share_button">Pay with F8D</a>
  </form>

<script type="text/javascript">
function placeOrder() {
      var title = document.getElementById('title_el').value;
      var desc = document.getElementById('desc_el').value;
      var price = document.getElementById('price_el').value;
      var img_url = document.getElementById('img_el').value;
      var product_url = document.getElementById('product_el').value;

	// Only send param data for sample. These parameters should be set
      // in the callback.
      var order_info = { "title":title,
                         "description":desc,
                         "price":price,
                         "image_url":img_url,
                         "product_url":product_url
                       };

      // calling the API ...
      var obj = {
            method: 'pay',
            order_info: order_info,
            purchase_type: 'item'
      };


	$('body').F8D.ui(obj);
}
</script>
<hr>
</body>
</html>
