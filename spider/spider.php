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
$is_spider = true;
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
	$db->query('DELETE FROM jb_spider WHERE html = "" OR title = "" OR date = "";');
	$db->query('DELETE FROM jb_spider WHERE html IN (
SELECT html FROM (
SELECT html,COUNT(*) FROM jb_spider
GROUP BY html
HAVING COUNT(*) > 1
) AS a
) LIMIT 1;');
	$db->query('ALTER TABLE `jb_spider` DROP `id`;ALTER TABLE `jb_spider` ADD `id` int NOT NULL FIRST;ALTER TABLE `jb_spider` MODIFY COLUMN `id` int NOT NULL AUTO_INCREMENT,ADD PRIMARY KEY(id);');
	$db->query("truncate table jb_spider_urls");
	$db->query('ALTER TABLE `jb_spider_urls` DROP `id`;ALTER TABLE `jb_spider_urls` ADD `id` int NOT NULL FIRST;ALTER TABLE `jb_spider_urls` MODIFY COLUMN `id` int NOT NULL AUTO_INCREMENT,ADD PRIMARY KEY(id);');
	exit('数据整理完成');
}

if (!$_GET['u'] && !$_GET['m']) {
	?>
<form action="" method="get">
	<input type="text" name="u" placeholder="http://开头，尽量不留/">
	<button type="submit">[开爬]</button>
</form>
<br />
<button type="button" onclick="window.location.href='?m=auto'"> [瞎几把爬爬] </button>
<button type="button" onclick="window.location.href='?m=manadata'"> [整理数据并清空爬行表] </button>
<button type="button" onclick="window.location.href='?m=logout'"> [登出] </button>
<br /><br />
剩余链接：
<?php
echo $db->query("select count(url) from jb_spider_urls")->fetch_row()[0]."条";
?>
</body>
</html>
<?php
	exit();
}

ob_end_flush();

while ($autoclimb>0 || $keepclimbing) :

if (file_exists("stop")) {
	exit("Stop by force");
}

if ($keepclimbing) {
	//一直爬
	file_put_contents("s/lastclimb", date("Y-m-d H:i:s"));
}

$autoclimb--;

whichurltoclimb();

$pageurl = $url;

$siteurl = getsiteurl($url);

$html = HTTPget($url);

preg_match("/Content-Type: (.*?)\s/", $html,$is_html);

if ($is_html[1] !== "text/html" && $is_html[1] !== "text/html;") {
	echo "Miss HTML MIME<br />";
	continue;
}

preg_match("/<.*?>/", $html,$is_html);

if (count($is_html[0]) == 0) {
	echo "Miss HTML content<br />";
	continue;
}

unset($is_html);

//去除curl输出的头部和第一个标签
$html = preg_replace("/^HTTP.*?\s</s", "<", $html);

preg_match_all("/<a.*?href=[\"|'](.*?)[\"|'| ]/", $html, $url);

$url = $url[1];

$url = array_unique($url);

$_url = [];

shuffle($url);

$limit = count($url)>50 ? 50 : count($url);

for ($i=0; $i < $limit; $i++) { 
	if (@substr($url[$i], 0, 1) !== "#" && !empty($url[$i])  &&
		@substr($url[$i], 0, 11) !== "javascript:") {
		$tmp = relative2absolute($url[$i]);
		if ($tmp && !in_array($tmp, $_url)) {
			$_url[] = $tmp;
		}
	}
}

unset($url,$limit);

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
$tmp_title = str_replace(" ", "", $htmltitle);
if (empty($tmp_title)) {
	$htmltitle = "*NoTitle*".$pageurl;
}
unset($tmp_title);
$html = preg_replace("/\s+/", " ", $html);
$html = preg_replace("/<(style|script).*?>.*?<\/(style|script)>/i", "", $html);
$html = preg_replace("/<(.*?)>/", "", $html);
if ($htmlencoding !== "UTF-8") {
	$html = iconv($htmlencoding, "UTF-8//IGNORE", $html);
}

if ($db->query("SELECT url FROM jb_spider WHERE url='".$pageurl."'")->num_rows == 0 || diffbtw2d($db->query("SELECT date FROM jb_spider WHERE url='".$pageurl."'")->fetch_row()[0],date("Y-m-d")) > 30) {
//库中不存在 或者 很久没爬过的链接

$rs = $db->query("INSERT INTO jb_spider (url,html,title,date) VALUES
('".$pageurl."','".fixmarks($html)."','".$htmltitle."','".date("Y-m-d")."')");

if ($db->query("SELECT url FROM jb_spider_urls WHERE url='".$pageurl."'")->num_rows !== 0) {
	//删除记录
	$db->query("delete from jb_spider_urls where url = '".$pageurl."'");
}

$remains = $db->query("select count(url) from jb_spider_urls")->fetch_row()[0];

if ($rs) {
	echo "Stored: ".$pageurl." &nbsp; Remains: ".$remains." <br>";
}

if ($remains > 5000) {
	$db->query("DELETE from jb_spider_urls order by rand() limit 2500");
}

}else{
	echo "Page Climbed".$pageurl;
}//库中不存在 或者 很久没爬过的链接 --- 入库

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
		if (substr($n, 0, 1) == "/") {
			return $siteurl.substr($n, 1);
		}else{
			return $siteurl.$n;
		}
	}
}
function resort($arr){
	$tmp = [];
	foreach ($arr as $key => $value) {
		$tmp[] = $value;
	}
	return $tmp;
}
function whichurltoclimb(){
	echo "\n";
	global $url,$db,$siteurl,$autoclimb;
	if ($_GET['u']) {
		$url = urldecode($_GET['u']);
		$autoclimb = -1;
	}else{
		if (empty($siteurl)) {
			$lastclimb = "<";
		}else{
			$lastclimb = $siteurl;
		}
		$url = $db->query("SELECT url FROM jb_spider_urls WHERE url NOT LIKE \"%".$lastclimb."%\" ORDER BY RAND() LIMIT 1")->fetch_row()[0];
		if (empty($url)) {
			$url = $db->query("SELECT url FROM jb_spider_urls ORDER BY RAND() LIMIT 1")->fetch_row()[0];
			if (empty($url)) {
				exit("URL remains : 0");
			}
		}
	}
}
function diffbtw2d($day1, $day2){
  $second1 = strtotime($day1);
  $second2 = strtotime($day2);
  if ($second1 < $second2) {
    $tmp = $second2;
    $second2 = $second1;
    $second1 = $tmp;
  }
  return ($second1 - $second2) / 86400;
}
if (!$keepclimbing) {
	file_put_contents("s/lastclimb", date("Y-m-d H:i:s"));
}
?>
<script type="text/javascript">
window.onload=function(){
	setTimeout(function(){
		window.location.href = "?m=auto";
	},5000);
}
</script>
</body>
</html>