<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SPIDER</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
</head>
<body>
<?php

error_reporting(E_ALL ^ E_NOTICE);
include 'config.php';

session_start();
if ($_GET['m'] == "logout") {
	$_SESSION['pass'] = "x";
	exit('已登出...');
}
if ($_POST['pass'] == $password) {
	$_SESSION['pass'] = $password;	
}
if ($_SESSION['pass'] !== $password) :
?>
<form action="" method="post">
	<input type="password" name="pass">
	<button type="submit">登陆</button>
</form>
</body>
</html>
<?php
exit();
endif;

if ($_GET['m'] == "install") :
$rs = $db->query("CREATE TABLE IF NOT EXISTS `jb_spider`(
   `id` INT UNSIGNED AUTO_INCREMENT,
   `url` VARCHAR(1024) NOT NULL,
   `html` VARCHAR(51200) NOT NULL,
   `title` VARCHAR(1024) NOT NULL,
   `date` DATE,
   PRIMARY KEY ( `id` )
)ENGINE = MyISAM DEFAULT CHARSET = utf8;");
if (!$rs) {
	exit("Error creating the table `jb_spider` !");
}
$rs = $db->query("CREATE TABLE IF NOT EXISTS `jb_spider_urls`(
   `id` INT UNSIGNED AUTO_INCREMENT,
   `url` VARCHAR(1024) NOT NULL,
   PRIMARY KEY ( `id` )
)ENGINE = MyISAM DEFAULT CHARSET = utf8;");
if (!$rs) {
	exit("Error creating the table `jb_spider_urls` !");
}
$rs = $db->query("ALTER TABLE jb_spider
ADD FULLTEXT (url),
ADD FULLTEXT (html);");
if (!$rs) {
	exit("FULLTEXT Error !");
}
exit("安装完毕 !");
endif;

if ($_GET['m'] == "manadata") {
	$db->query('ALTER TABLE `jb_spider` DROP `id`;ALTER TABLE `jb_spider` ADD `id` int NOT NULL FIRST;ALTER TABLE `jb_spider` MODIFY COLUMN `id` int NOT NULL AUTO_INCREMENT,ADD PRIMARY KEY(id);');
	exit('数据整理完成');
}

if (!$_GET['u'] && !$_GET['m']) {
	?>
<form action="" method="get">
	<input type="text" name="u" placeholder="http://">
	<button type="submit">[开爬]</button>
</form>
<br />
<button type="button" onclick="window.location.href='?m=auto'"> [瞎几把爬爬] </button>
<button type="button" onclick="window.location.href='?m=manadata'"> [整理数据] </button>
<button type="button" onclick="window.location.href='?m=logout'"> [登出] </button>
</body>
</html>
<?php
	exit();
}

ob_end_flush();

while ($autoclimb>0 || $keepclimbing) :

$autoclimb--;

whichurltoclimb();

$pageurl = $url;

$siteurl = getsiteurl($url);

$html = HTTPget($url);

preg_match("/Content-Type: (.*?)\s/", $html,$is_html);

if ($is_html[1] !== "text/html" && $is_html[1] !== "text/html;") {
	continue;
}

unset($is_html);

//去除curl输出的头部和第一个标签
$html = preg_replace("/^HTTP.*?\s</s", "<", $html);

preg_match_all("/<a.*?href=[\"|'](.*?)[\"|'| ]/", $html, $url);

$url = $url[1];

$url = array_unique($url);

$_url = [];

for ($i=0; $i < count($url); $i++) { 
	if (@substr($url[$i], 0, 1) !== "#" && !empty($url[$i])  &&
		@substr($url[$i], 0, 11) !== "javascript:") {
		$tmp = relative2absolute($url[$i]);
		if ($tmp && !in_array($tmp, $_url)) {
			$_url[] = $tmp;
		}
	}
}

unset($url);

// 检查重复

$tmp_limit = count($_url);

for ($i=0; $i < $tmp_limit; $i++) { 
	if ($db->query("SELECT url FROM jb_spider WHERE url='".$_url[$i]."'")->num_rows !== 0 ||
		$db->query("SELECT url FROM jb_spider_urls WHERE url='".$_url[$i]."'")->num_rows !== 0) {
		unset($_url[$i]);
	}
}

unset($tmp_limit);

// 检查重复 -----

$_url = resort($_url); //重新排列

$tmp_values = "";

if (count($_url)>0) :

for ($i=0; $i < count($_url); $i++) { 
	$tmp_spr = $i == 0 ? "" : ",";
	$tmp_values .= $tmp_spr. "('".$_url[$i]."')";
}

$db->query("INSERT INTO jb_spider_urls (url) VALUES ".$tmp_values);
unset($tmp_values,$tmp_spr);

endif;

$html = str_replace(PHP_EOL, "", $html);
preg_match("/<meta.*?charset=.{0,1}(.*?)[;|\"|']/i", $html, $htmlencoding);
$htmlencoding = strtoupper($htmlencoding[1]);
if (empty($htmlencoding)) {
	$htmlencoding = "UTF-8";
}
preg_match("/<title>(.*?)<\/title>/i", $html, $htmltitle);
$htmltitle = $htmltitle[1];
if (empty($htmltitle)) {
	$htmltitle = "*NoTitle*".$pageurl;
}
$html = preg_replace("/\s+/", " ", $html);
$html = preg_replace("/<(style|script).*?>.*?<\/(style|script)>/i", "", $html);
$html = preg_replace("/<(.*?)>/", "", $html);
if ($htmlencoding !== "UTF-8") {
	$html = iconv($htmlencoding, "UTF-8//IGNORE", $html);
}

if ($db->query("SELECT url FROM jb_spider WHERE url='".$pageurl."'")->num_rows == 0) :

$rs = $db->query("INSERT INTO jb_spider (url,html,title,date) VALUES
('".$pageurl."','".fixmarks($html)."','".$htmltitle."','".date("Y-m-d")."')");

if ($db->query("SELECT url FROM jb_spider_urls WHERE url='".$pageurl."'")->num_rows !== 0) {
	//删除记录
	$db->query("delete from jb_spider_urls where url = '".$pageurl."'");
}

$remains = $db->query("select count(url) from jb_spider_urls")->fetch_row()[0];

if ($rs) {
	echo "Page stored: ".$pageurl."<br />Remains: ".$remains." <br>";
}

if ($remains > 5000) {
	$db->query("delete from jb_spider_urls order by rand() limit 2500");
}

endif;

flush();

endwhile;

function HTTPget($u){
	$fakeip = rand(0,190).".".rand(0,255).".".rand(0,255).".".rand(0,255);
	$h = array("User-Agent: Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html",'CLIENT-IP:'.$fakeip, 
'X-FORWARDED-FOR:'.$fakeip);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $u);
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 6666);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, false);
	curl_setopt($ch, CURLOPT_HEADER, true);
	$content = curl_exec($ch);
	curl_close($ch);
	if ($content == false) {
    	return "Get content false!";
	}
	return $content;
}
function relative2absolute($n){
	global $siteurl;
	if (strpos($n,"#") !== false) {
		$n = substr($n, 0, strpos($n,"#"));
	}
	if (substr($n, 0, 7) == "http://" || substr($n, 0, 8) == "https://") {
		return $n;
	}elseif (substr($n, 0, 2) == "//") {
		return "http:".$n;
	}else{
		if (empty($n)) {
			return false;
		}
		return $siteurl."/".$n;
	}
}
function resort($arr){
	$tmp=[];
	foreach ($arr as $key => $value) {
		$tmp[] = $value;
	}
	return $tmp;
}

function whichurltoclimb(){
	global $url,$db;
	if ($_GET['u']) {
		$url = urldecode($_GET['u']);
		if (empty($url)) {
			exit("URL not found");
		}
	}else{
		$url = $db->query("SELECT url FROM jb_spider_urls ORDER BY RAND() LIMIT 1")->fetch_row()[0];
		if (empty($url)) {
			exit("URL remains : 0");
		}
	}
}

?>
<div id="output"></div>
<script type="text/javascript">
window.onload=function(){
	setTimeout(function(){
		window.location.href = "?m=auto";
	},5000);
}
</script>
</body>
</html>