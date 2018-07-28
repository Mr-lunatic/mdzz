/*! Copyright (c) 2018 Luuljh (https://mdzz.lljh.bid)
 * Version: 0.0.2
 */
function mdzz(u,options){
	if (u==true) {
		//仅执行一次
		mdzz_page_url = window.location.protocol+"//"+window.location.host;
		var mdzz_df_options={
			field: "body",
			fieldnum: 0,
			container: document.getElementsByTagName('body')[0],
			add: false,
			sending: function(){},
			complete: function(){},
			crosssite: false,
			oncrosssite: function(){},
			onerror:function(s){/*xhstatus*/},
			cache: false //禁止ajax缓存
		}
		for(var key in mdzz_df_options){
    		if(options.hasOwnProperty(key)!==true){
        		options[key]=mdzz_df_options[key];
			}
		}
		mdzz_options = options;
		//基本设置完成
		if (window.location.hash!=="") {
			mdzz_go(window.location.hash.replace(/#/g,""));
		}
		mdzz_a();
	}else{
		//这里的options用来传输a标签的href属性
		//点击了超链接
		mdzz_xhurl = "#"+options;
		mdzz_xhurl = mdzz_xhurl.replace(mdzz_page_url,"");
		window.location.hash = mdzz_xhurl;
		return false;//结束跳转
	}
}

window.onhashchange = function(){
	mdzz_go(window.location.hash.replace(/#/g,""));
}

if (!Object.keys) Object.keys = function(o) {
  if (o !== Object(o))
    throw new TypeError('Object.keys called on a non-object');
  var k=[],p;
  for (p in o) if (Object.prototype.hasOwnProperty.call(o,p)) k.push(p);
  return k;
}

function mdzz_a(){
	var mdzz_tmp = document.querySelectorAll("a");
	for (var i = 0; i < mdzz_tmp.length; i++) {
		if (!mdzz_tmp[i].hasAttribute("mdzz") && mdzz_tmp[i].href!=="#" && !/^javascript:/i.test(mdzz_tmp[i].href)) {//排除
			mdzz_tmp[i].onclick=function(){
				return mdzz(false,this.href);
			}
		}
	}
}

function mdzz_go(u){
	if (!mdzz_options.crosssite) {
		//禁止跨站检测
		if (/^(http:\/\/|\/\/|https:\/\/)/i.test(u)) {//外部URL
			var mdzz_tmp_reg  = /\/\/(.*?)\.(.*?)\//i.exec(u);
			if (mdzz_tmp_reg !== null) {
				// 获取到如：//a.mdzz.js/  二次匹配出域名
				mdzz_tmp_reg = /\/\/(.*?)\//i.exec(mdzz_tmp_reg)[1];
			}else{
				//不以 / 结尾的 //a.mdzz.js
				mdzz_tmp_reg  = /\/\/(.*?\..*)/i.exec(u)[1];
			}
			//获取到玉米了
			if (mdzz_tmp_reg !== window.location.host) {//不是我家的玉米！
				mdzz_options.oncrosssite();
				return true;
			}
		}
	}
	if (window.location.pathname == window.location.hash.replace(/#/ig,"")) {
		window.location.hash = "";
		return true;
	}
	var xmlhttp = new XMLHttpRequest();
	if (u == '') {u = window.location.pathname;}
	if (!mdzz_options.cache) { //无缓存模式
		if (navigator.appName=="Microsoft Internet Explorer" && /MSIE 8/i.test(navigator.appVersion)) {
			// ie8
			if (/\?/g.test(u)) {u=u+"&"}else{u=u+"?"}
			u = u+"mdzz_no_cache="+Math.random()*Math.random();
			xmlhttp.open("GET",u,true);
		}else{
			xmlhttp.open("GET",u,true);
			xmlhttp.setRequestHeader("Cache-Control","no-cache");
		}
	}else{
		xmlhttp.open("GET",u,true);
	}
	xmlhttp.onreadystatechange=function(){
		if (xmlhttp.readyState==4){
			//c
			if (xmlhttp.status!==200 && xmlhttp.status !== 301 && xmlhttp.status !== 302) {
				mdzz_options.onerror(xmlhttp.status);
			}else{
				var mdzz_tmp_html = mdzz_str2dom(xmlhttp.responseText).querySelectorAll(mdzz_options.field);
				if (mdzz_tmp_html.length==0) {
					var mdzz_tmp_html = xmlhttp.responseText;
				}else{
					mdzz_tmp_html = mdzz_tmp_html[mdzz_options.fieldnum].innerHTML
				}
				if (mdzz_options.add) {
					mdzz_options.container.innerHTML+=mdzz_tmp_html;
				}else{
					mdzz_options.container.innerHTML=mdzz_tmp_html;
				}
			}
			mdzz_a();
			mdzz_options.complete();
		}else if (xmlhttp.readyState==2) {
			//s
			mdzz_options.sending();
		}
	}
	xmlhttp.send();
}

function mdzz_str2dom(h){
	var obj = document.createElement("div");
	obj.innerHTML = h;
	return obj;
}
