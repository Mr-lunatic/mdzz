<?php
/**
* @author Luuljh <http://blog.lljh.bid>
* @license GPL-2.0 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
*/
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

if ($_GET['s'] || $_GET['id'] || $_GET['go']):
	include 'config.php';
endif;
if ($_GET['go']) {
	htmlinfo('<meta content="always" name="referrer"><script>window.location.replace("'.urldecode($_GET['go']).'")</script>
<noscript><META http-equiv="refresh" content="0;URL=\''.urldecode($_GET['go']).'\'"></noscript>');
	exit();
}
if ($_GET['m']=="cache" && $_GET['id']) {
	!is_nan($_GET['id']) ?: exit('What are you doing ?');
	htmlinfo($db->query("SELECT html FROM jb_spider WHERE id=".$_GET['id'])->fetch_row()[0]);
	exit();
}
$wd = htmlspecialchars(urldecode($_GET['s']));
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>JB Search</title>
	<link rel="stylesheet" type="text/css" href="s/i.css">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<script src="//libs.baidu.com/jquery/1.8.3/jquery.min.js"></script>
</head>
<body>

<?php if (empty($wd)): ?>
	<img src="s/logo.jpg" class="logo" ondragstart='return false;'>
<?php endif ?>
<form action="" method="get">
	<input type="input" name="s" value="<?php echo $wd ?>">
	<button type="submit">立即搜索</button>
</form>

<?php
if (!empty($wd)):
	$s = fixmarks($wd);
	if (strpos($s, " ") !== false) {
		$s = explode(" ", $s);
		$gs = $s;
		$s = implode("%", $s);
	}else{
		$gs[0] = $s;
	}
	$pageNo = $_GET['page'];
	$pageNo ?: $pageNo = 1;
	if (is_nan($pageNo) || $pageNo<1) {
		echo "<div class='warning'>请勿修改page参数</div>";
		$pageNo = 1;
	}
	$pageNo--;
	$banid = "id <> -1";
	$tmp_addon = $pageNo*$searchlimit;
	$tmp_wd = str_replace(" ", "|", $wd);
	if (substr($tmp_wd, 0, 1) == "|") {
		$tmp_wd = substr($tmp_wd, 1);
	}
	if (substr($tmp_wd, -1) == "|") {
		$tmp_wd = substr($tmp_wd,0,strlen($tmp_wd)-1); 
	}
	$tmp_banid_ = $db->query("SELECT id FROM jb_spider WHERE concat(url,title,html) like '%".$s."%' ORDER BY url REGEXP '(".$tmp_wd.")' desc, title REGEXP '(".$tmp_wd.")' desc, date desc limit ".$tmp_addon);
	while ($tmp_banid = $tmp_banid_->fetch_row()) {
		$banid .= " AND id <>".$tmp_banid[0];
	}
	unset($tmp_banid_,$tmp_banid,$tmp_addon);
	$rs = $db->query("SELECT * FROM jb_spider WHERE concat(url,title,html) like '%".$s."%' AND ".$banid." ORDER BY url REGEXP '(".$tmp_wd.")' desc, title REGEXP '(".$tmp_wd.")' desc, date desc limit ".$searchlimit);
	unset($tmp_wd);
	$count = 0;
	while($tmp = $rs->fetch_row()){
		if ($count >= $searchlimit) {
			break;
		}
		$count++;
		$info[$count]['id'] = $tmp[0];
		$info[$count]['url'] = $tmp[1];
		$info[$count]['content'] = $tmp[2];
		$info[$count]['title'] = $tmp[3];
		$info[$count]['date'] = $tmp[4];
		$info[$count]['pr'] = 0;
		for ($i=0; $i < count($gs); $i++) {
			//计算权重
			if (preg_match("/".$gs[$i]."/i", $info[$count]['title'])) {
				$info[$count]['pr'] += 5;
			}
			if (preg_match("/".$gs[$i]."/i", $info[$count]['url'])) {
				$info[$count]['pr'] += 5;
			}
			$strrepeatcount = substr_count($info[$count]['content'],$gs[$i]);
			if ($strrepeatcount>1 && $strrepeatcount<35) {
				$info[$count]['pr'] += $strrepeatcount;
			}
			$tmp_title = str_replace("*NoTitle*", "", $info[$count]['title']);
			if ($info[$count]['title'] !== $tmp_title) {
				//无标题
				$info[$count]['title'] = $tmp_title;
				$info[$count]['pr'] -= 1;
			}
		}
	}
	unset($strrepeatcount);
	if ($count !== 0) :
	//按照权重重新排序
	foreach ($info as $key => $row){
        $volume[$key]  = $row['pr'];
        $edition[$key] = $row['date'];
    }
    array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $info);
	for ($i=0; $i < count($info); $i++) : ?>

<div class="rs">
	<a class="title" href="?go=<?php echo urlencode($info[$i]['url']) ?>" target="_blank">
		<?php echo $info[$i]['title'] ?>
	</a>
	<div class="content"><?php echo excerpt($info[$i]['content']) ?></div>
	<div class="ops">
		<a class="a" href="?go=<?php echo urlencode($info[$i]['url']) ?>" target="_blank">
			<?php echo getsiteurl($info[$i]['url']) ?>
		</a>
		<a target="_blank" href="?m=cache&id=<?php echo $info[$i]['id'] ?>">查看快照</a>
		<span><?php echo $info[$i]['date'] ?></span>
		<span>动态权重：<?php echo $info[$i]['pr'] ?></span>
	</div>
</div>

<?php
	endfor;
else:
?>
<div class="rs">
	<div class="title">
		抱歉哦~什么都没有搜索到啊 QAQ
	</div>
</div>
<?php
endif;

endif;

function excerpt($str){
	global $gs;
	$start = stripos($str, $gs[0]);
	$sub = substr($str,$start, 300);
	return $sub."...";
}

?>

<?php if (!empty($wd)) : ?>
<div class="nav">
	<?php if ($pageNo>0): ?>
		<a class="f-l" href="?s=<?php echo $wd ?>&page=<?php echo $pageNo ?>"><li>上一页</li></a>
	<?php endif ?>
	<?php if ($count >= $searchlimit) : ?>
	<a class='f-r' href="?s=<?php echo $wd ?>&page=<?php echo $pageNo+2 ?>"><li>下一页</li></a>
	<?php endif ?>
</div>
<?php endif ?>
<script type="text/javascript" src="s/i.js"></script>
<small class="foot">
&copy; 2018 <?php echo $_SERVER['HTTP_HOST'] ?>
. Powered By <a href="https://github.com/1443691826/mdzz/tree/master/spider">Jbsearch</a>
<?php
if (file_exists("s/lastclimb"))
	echo "<br />最后一次爬行：".file_get_contents("s/lastclimb")
?>
</small>
<!--
底部链接禁止去除
-->
</body>
</html>