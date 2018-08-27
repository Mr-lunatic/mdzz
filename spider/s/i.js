$(function(){


wd = $("input[name=s]").val().split(" ");
for (var i = 0; i < wd.length; i++) {
	$(".title").each(function(){
		$(this).text(
			$(this).text().replace(new RegExp(wd[i],"i"),"ｊｂｍａｒｋ"+wd[i]+"ｊｂｍａｒｋ")
		)
	});
	$(".content").each(function(){
		$(this).text(
			$(this).text().replace(new RegExp(wd[i],"i"),"ｊｂｍａｒｋ"+wd[i]+"ｊｂｍａｒｋ")
		)
	});
}
$(".title").each(function(){
	$(this).html(
			$(this).html().replace(/ｊｂｍａｒｋ(.*?)ｊｂｍａｒｋ/g,
				"<span class='mark'>$1</span>")
	)
});
$(".content").each(function(){
	$(this).html(
			$(this).html().replace(/ｊｂｍａｒｋ(.*?)ｊｂｍａｒｋ/g,
				"<span class='mark'>$1</span>")
	)
});

});
console.log("Jb 搜索引擎：%s","https://github.com/1443691826/mdzz/tree/master/spider");