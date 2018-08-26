<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SPIDER</title>
</head>
<body>
<?php
//options
error_reporting(E_ALL ^ E_NOTICE);
include 'config.php';

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
exit("OJBK !");
endif;

if (!$_GET['u'] && !$_GET['m']) {
	?>
<form action="" method="get">
	<input type="text" name="u" placeholder="http://">
	<button type="submit">[Ok]</button>
	<br />
	<button type="button" onclick="window.location.href='?m=auto'">[auto]</button>
</form>
<?php
	exit("Missing `u` -- the url !");
}

ob_end_flush();

while ($autoclimb>0 || $keepclimbing) :

$autoclimb--;

whichurltoclimb();

$pageurl = $url;

$siteurl = getsiteurl($url);

$html = HTTPget($url);

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
	$htmltitle = "无title的页面";
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

if ($rs) {
	echo "Page stored: ".$pageurl."<br />Urls remains: ".
	$db->query("select count(url) from jb_spider_urls")->fetch_row()[0]
	." <br>";
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
	curl_setopt($ch, CURLOPT_HEADER, $h);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
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
	window.location.href = "?m=auto";
}
</script>
</body>
</html>