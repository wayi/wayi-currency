/*
*      f8d.js
*      version: 2.0.0
*      updated: 2012/2/7
*/

function getConfig(app){
	//default
	var config = { 
		f8d_web:	'http://fun.fun.wayi.com.tw/',
		f8d_api:	'http://api.fun.wayi.com.tw/'
	};

	if(app && app.f8d)
		config.f8d_web = app.web;
	if(app && app.api)
		config.f8d_api = app.api;
	return config;
}

(function($) {
	var FLAG_INITIAL = false;
	var FLAG_GET_APP_ENV = false;
	var config = getConfig('');
	var F8D_PLUGIN_VERSION = '2.0.1';


	jQuery.fn.extend({ curReturn: null, jQueryInit: jQuery.fn.init });

	jQuery.fn.extend({
		init: function( selector, context ) {
			jQuery.fn.curReturn = new jQuery.fn.jQueryInit(selector, context);
			return jQuery.fn.curReturn;
		}
	});

	jQuery.extend({
		namespaceData: {},
		namespaceExtend: function(NameSpaces){
			if(eval(NameSpaces) != undefined){ $.extend(eval(NameSpaces), {}); }else{ eval(NameSpaces + " = {};"); }
		},
		namespace: function(namespaces, objects, inherited){
			if(typeof objects == "function"){
				if(namespaces.match(".")){
					nss = namespaces.split(".");
					snss = "";
					for(var i = 0; i < nss.length; i++){
						snss += "['" + nss[i] + "']";

						jQuery.namespaceExtend("jQuery.namespaceData" + snss);
						jQuery.namespaceExtend("jQuery.fn" + snss);
					}
					eval("jQuery.namespaceData" + snss + " = objects;");

					eval("jQuery.fn" + snss + " = " +
						"function(){ return eval(\"jQuery.namespaceData" + snss + 
							((inherited)? ".apply" : "") + "(jQuery.fn.curReturn, arguments)\"); }");

				}else{
					jQuery.extend({
						namespaces: function(){
							return objects(jQuery.fn.curReturn);
						}
				});
				}
		}else{
			if(arguments.length < 3) inherited = objects['inherited'] == true;        
			for(var space in objects){
				jQuery.namespace(namespaces + "." + space, objects[space], inherited);
		};
		}
		}
});

/*	
*	F8D 
*/ 
var appid;
var access_token;
$.namespace("F8D",{
	init: function(elems, options){
		FLAG_INITIAL = true;
		//validate
		appid = options[0].appid;
		access_token = options[0].access_token;
		if(isNaN(appid)){
			alert("appid is invalid");
			return;
		}
		if(access_token === undefined){
			alert('access token is undefined');
			return;
		}

			FLAG_GET_APP_ENV = true;



	},
	ui: function(elems, options){
		//make sure that app env is loaded
		var getAppEnv = setInterval(function(){
			if(FLAG_GET_APP_ENV){
				clearInterval(getAppEnv);

				//setup ui
				jQuery('head').append('<script type="text/javascript" src="http://api.fun.wayi.com.tw/assets/jqplugin/jquery.ba-postmessage.min.js?v=20111020"/>');
					jQuery('head').append('<link rel="stylesheet" type="text/css" href="'+config.f8d_api+'assets/jqplugin/f8d.css?v=20120109" />');
					options[0].src = config.f8d_api + 'ui';
					options[0].to = 'small'; //small dialog
					options[0].dialogName = 'F8D_dialog';

					var dialog = setupDialog(options[0]);
					$(elems).append($(dialog));

					//callback
					var interval = setInterval(function() {
						if (eval("typeof jQuery.receiveMessage" ) != 'undefined') {
							clearInterval(interval);
							$.receiveMessage(
								function(e){
									//$('#'. options[0].dialogName).remove();
									if(typeof(options[1])!='undefined') 
									{							
										var respon = $.parseJSON(e.data);
										if(respon.cmd && respon.cmd == "resize"){
											var height = respon.height;
											var width = respon.width;
											dialog_resize(height,width);
										}else{
											$('#F8D_dialog').remove();
											var callback = options[1];
											callback(respon);						  		
										}
									}
								}
					);	    
						}
			}, 50);		

			}
	},50);

	}
});

String.format = function(src){
	if (arguments.length == 0) return null;
	var args = Array.prototype.slice.call(arguments, 1);
	return src.replace(/\{(\d+)\}/g, function(m, i){
		return args[i];
});
};

function dialog_resize(height, width){
	$('#F8D_dialog').children(".bg").height(height+21);
	$("#iframe-F8D_dialog").height(height);
	$("#iframe-F8D_dialog").width(width);
	$('#F8D_dialog').children(".bg").width(width+21);

}

function setupDialog(options){
	//style	
	var defaults = {max:15,hostname:location.hostname};
	var settings = $.extend(defaults, options);

	settings.zindex = (options.zindex === undefined || isNaN(options.zindex) )? 1000:options.zindex;
	settings.height = 155;// (options.height === undefined || isNaN(options.height))? 155:options.height;
	settings.width = 450; //(options.width === undefined || isNaN(options.width))? 425:options.width;
	settings.appid = appid;
	settings.access_token = access_token;
	settings.domain = window.location.protocol + "//" +window.location.hostname;
	settings.version = F8D_PLUGIN_VERSION;

	//setup dialog
	$('#'+settings.dialogName).remove();
	var class_name='layout_to_box';
	if(typeof(options.to)=='undefined')
	{
		class_name = 'layout_box';
	}
	var iframeSrc = options.src + '?' + $.param(settings).toString();
	var dialog = '<div id="{0}" name="invite_page_touch" class="{1}"  style="z-index:{2}px" ><a name="invite_area"></a><div class="bg" style="z-index:{3}px"></div><div class="data" style="z-index:{4}px"><iframe id="iframe-{0}" style="width:{5}px; height:{6}px;"  scrolling="no" frameborder="0" src="{7}"></iframe></div></div>';
	return String.format(dialog, settings.dialogName, class_name, settings.zindex, settings.zindex+1, settings.zindex+2,settings.width, settings.height, iframeSrc);
} 

$.namespace("fun", {
	invites: function(elems, options){
		if(!isInit())
			return;
		var interval = setInterval(function(){
			if(FLAG_GET_APP_ENV){
				clearInterval(interval);

				return invites(elems[0], options);
			}
		},50);
	}
});

$.namespace("fun.iframe", {
	setHeight: function(elems, options){
		return setHeight(options[0]);
},
setAutoResize: function(elems){
	var height = document.body.offsetHeight + 20;
	return setHeight(height);
}
});
function isInit(){
	if(!FLAG_INITIAL ){
		alert('please initial f8d js first. \r\nexample: $("body").init({"appid":APP_ID,"access_token":ACCESS_TOKEN})');
	}
	return FLAG_INITIAL;
}
$.namespace("fun.ui", {
	invite: function(elems, options){
		if(!isInit())
			return;
			
		var interval = setInterval(function(){
			if(FLAG_GET_APP_ENV){
				clearInterval(interval);

				jQuery('head').append('<link rel="stylesheet" type="text/css" href="'+config.f8d_api+'assets/socialplugin/css/apprequest.css?v=20111020" />');
				return apprequest(elems[0], options);
			}
		},50);
	}	
});

function setHeight(height) {
	var funProxy = "http://fun.wayi.com.tw/proxy.html";
	if(!$("#funProxy").length){
		$('<iframe id="funProxy" style="display:none;"><\/iframe>').appendTo('body');
	}
	$("#funProxy").attr("src",funProxy + '?t='+ new Date().getTime()+'#method=setIframeSize&height=' + height);
};

function invites(elems,options) {
	if(options.token_key == 'undefined' || options.token_screet == 'undefined') return;
	var defaults = {max:15};
	var settings = $.extend(defaults, options[0]);
	settings = $.param(settings).toLowerCase();

	var iframe = $('<iframe id="funInvite" style="width:720px; height:560px;"  scrolling="no" frameborder="0" src="i' + config.f8d_api+'invites?' + settings + '"><\/iframe>');
	$(elems).append(iframe);
};

function apprequest(elems,options){
	jQuery('head').append('<script type="text/javascript" src="http://api.fun.wayi.com.tw/assets/jqplugin/jquery.ba-postmessage.min.js?v=20111020"/>');
		if(options[0].access_token === undefined){
			showMessage('access token is undefined');
			return;
		}

		//style	
		var defaults = {max:15,hostname:location.hostname};
		var settings = $.extend(defaults, options[0]);
		settings.zindex = (options[0].zindex === undefined )? 1000:options[0].zindex;
		settings.zindex = isNaN(settings.zindex)? 1000:settings.zindex;

		settings = $.param(settings).toLowerCase();
		$('#invite_page_touch').remove();
		var class_name='layout_to_box',h=205;
		if(typeof(options[0].to)=='undefined')
		{
			class_name = 'layout_box';
			h = 524;
		}
		var iframe = $('<div id=\'invite_page_touch\' name=\'invite_page_touch\' class=\''+class_name+'\'  style=\'z-index:'+settings.zindex +'\' ><a name="invite_area"></a><div class=\'bg\' style=\'z-index:'+(settings.zindex +1)+'\' ></div><div class=\'data\' style=\'z-index:'+(settings.zindex+2)+'\'><iframe id="funInvite" style="width:575px; height:'+h+'px;"  scrolling="no" frameborder="0" src="'+config.f8d_api+'apprequest?' + settings + '"><\/iframe></div></div>');
		//var iframe = window.open('http://10.2.0.25/funci/invite/apprequest?' + settings ,'invite_page_touch');
			$(elems).append(iframe);	
			location.hash = "invite_area";	

			var interval = setInterval(function() {
				if (eval("typeof jQuery.receiveMessage" ) != 'undefined') {
					clearInterval(interval);
					$.receiveMessage(
						function(e){
							$('#invite_page_touch').remove();
							if(typeof(options[1])!='undefined') 
							{
								var callback = options[1];
								var respon = $.parseJSON(e.data);
								callback(respon);						  		
							}
							//$.each(metest, function(index, value) {$("body").append(index + ":" + value+"<br />");});			  	
						}
						//,'http://10.2.0.25/funci/invite/apprequest'
					);	    
				}
}, 50);		

}

function showMessage(msg, level){
	document.write(msg);	
}
})(jQuery);

