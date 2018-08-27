<?php

$autoclimb = 4; //自动爬行次数
$keepclimbing = false;//一直保持爬行到最大执行时间
$searchlimit = 30;//每页搜索限制N条
$password = "admin888";//蜘蛛的密码
ignore_user_abort(true);//关掉页面也会继续执行
set_time_limit(30);
$dbhost = "localhost";
$username = "root";
$userpass = "root";
$dbdatabase = "root";
date_default_timezone_set("PRC");
$db = new mysqli($dbhost,$username,$userpass,$dbdatabase);
if (mysqli_connect_error()) {
	exit('Could not connect to database.');
}

function fixmarks($str){
	return str_replace(["\"","'"], ["\\\"","\\'"], $str);
}
function getsiteurl($n){
	preg_match("/^(?=^.{3,255}$)(http(s)?:\/\/)?(www\.)?[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+(:\d+)*(\/\w+\.\w+)*/", $n, $rs);
	return $rs[0];
}
function htmlinfo($str){
	echo '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
</head>
<body>
'.$str.'
</body>
</html>';
}