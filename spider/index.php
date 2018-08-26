<?php
/**
* @author Luuljh <http://blog.lljh.bid>
* @license GPL-2.0 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
*/
error_reporting(E_ALL ^ E_NOTICE);
if ($_GET['s'] || $_GET['id']):
	include 'config.php';
endif;
if ($_GET['go']) {
	header("Location: ".urldecode($_GET['go']));
	exit();
}
if ($_GET['m']=="cache" && $_GET['id']) {
	!is_nan($_GET['id']) ?: exit('What are you doing ?');
	htmlinfo($db->query("SELECT html FROM jb_spider WHERE id=".$_GET['id'])->fetch_row()[0]);
	exit();
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>JB Search</title>
	<link rel="stylesheet" type="text/css" href="i.css">
</head>
<body>

<form action="" method="get">
	<input type="input" name="s" value="<?php echo htmlspecialchars(urldecode($_GET['s'])) ?>">
	<button type="submit">[search]</button>
</form>

<?php
if ($_GET['s']):

	$s = fixmarks(htmlspecialchars(urldecode($_GET['s'])));
	if (strpos($s, " ") !== false) {
		$s = explode(" ", $s);
		$gs = $s;
		$s = implode("% %", $s);
	}else{
		$gs[0] = $s;
	}
	$rs = $db->query("SELECT * FROM jb_spider WHERE concat(title,html) like '%".$s."%'");
	$count = 0;
	while($tmp = $rs->fetch_row()){
		if ($count >= 30) {
			break;
		}
		$count++;
		$info[$count]['id'] = $tmp[0];
		$info[$count]['url'] = $tmp[1];
		$info[$count]['content'] = excerpt($tmp[2]);
		$info[$count]['title'] = $tmp[3];
		$info[$count]['date'] = $tmp[4];
		$info[$count]['pr'] = 0;
		for ($i=0; $i < count($gs); $i++) { 
			if (preg_match("/".$gs[$i]."/i", $info[$count]['title'])) {
				$info[$count]['pr']++;
			}
		}
	}
	if ($count !== 0) :
	//按照权重重新排序
	foreach ($info as $key => $row){
        $volume[$key]  = $row['pr'];
        $edition[$key] = $row['date'];
    }
    array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $info);
	for ($i=0; $i < count($info); $i++) : ?>

<div class="rs">
	<div class="title">
		<a href="?go=<?php echo urlencode($info[$i]['url']) ?>" target="_blank">
			<?php echo $info[$i]['title'] ?>
		</a>
	</div>
	<div class="content"><?php echo $info[$i]['content'] ?></div>
	<div class="ops">
		<a class="a" href="?go=<?php echo urlencode($info[$i]['url']) ?>" target="_blank">
			<?php echo getsiteurl($info[$i]['url']) ?>
		</a>
		<span><?php echo $info[$i]['date'] ?></span>
		<a href="?m=cache&id=<?php echo $info[$i]['id'] ?>">查看快照</a>
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
	$tmp = $gs[0];
	$start = stripos($str, $tmp);
	$sub = substr($str, $start, 200);
	return $sub."...";
}

?>
<small class="ct mg">&copy; 2018.</small>
</body>
</html>