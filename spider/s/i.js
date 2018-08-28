$(function(){

wd = $("input[name=s]").val().split(" ");
for (var i = 0; i < wd.length; i++) {
	$(".title").each(function(){
		$(this).text(
			$(this).text().replace(new RegExp(wd[i],"i"),"ｊｂＭ"+wd[i]+"ｊｂＭ")
		)
	});
	$(".content").each(function(){
		$(this).text(
			$(this).text().replace(new RegExp(wd[i],"i"),"ｊｂＭ"+wd[i]+"ｊｂＭ")
		)
	});
	$(".ops .a").each(function(){
		$(this).text(
			$(this).text().replace(new RegExp(wd[i],"i"),"ｊｂＭ"+wd[i]+"ｊｂＭ")
		)
	});
}
$(".title").each(function(){
	$(this).html(
			$(this).html().replace(/ｊｂＭ(.*?)ｊｂＭ/g,
				"<span class='mark'>$1</span>")
	)
});
$(".content").each(function(){
	$(this).html(
			$(this).html().replace(/ｊｂＭ(.*?)ｊｂＭ/g,
				"<span class='mark'>$1</span>")
	)
});
$(".ops .a").each(function(){
	$(this).html(
			$(this).html().replace(/ｊｂＭ(.*?)ｊｂＭ/g,
				"<b>$1</b>")
	)
});

});
console.log("Jb 搜索引擎：%s","https://github.com/1443691826/mdzz/tree/master/spider");