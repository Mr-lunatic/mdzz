妈的智障.Js (MDZZ.Js)
------
*名字是瞎几把拼凑的...*

真实滑天下之大稽，没有任何一个JS库比这个更加智障了！

我是一款鸡肋的模仿PJAX和网易云音乐网页版的JS库

可以通过监控window.location.hash的变化来无刷新改变网页内容

0.0.1版本仅3.33KB的大小 (๑•̀ㅂ•́)و✧

共计：98个汉字 3227个字符 包含：72个汉字 2个全角标点 3053个字母 26个数字。
作为一个有尊严的JS库，我内心很有逼数，甚至还支持IE8！！！

如果你不知道我可以用来干什么的话，我也只有嘤嘤嘤了 (┬＿┬)。

查看Demo: [在线demo](./demo/)

好啦好啦，少侠快随我来，马上习得mdzz.JS吧 =-=

无需任何额外的库，在body闭合前引用mdzz.JS后，在页脚插♂入以下js代码即可：

```
var mymdzz = new mdzz(true,{ //new是为了霸气一点点...
    field: "#m", //使用CSS选择器选择[AJAX请求到的页面]的元素 默认为body
    fieldnum: 0, //选取第几个元素？ [0, +∞) 默认为0
    container: $("#233"), //用来存放[AJAX得到的HTML]的容器 默认为body
    add: false,//加法模式 开启后不会清空container里面的内容再 默认为false
    cache: false, //是否启用缓存
    complete: function(){
        //AJAX请求完成时执行的代码
    },
    sending: function(){
        //AJAX请求发送时执行的代码
    },
    crosssite: false, //禁止跨站 防止恶意攻击 默认为false
    oncrosssite: function (){
        alert("这不是我家的玉米！"); //跨站时执行的代码
    },  
    onerror: function(s){
        alert("抱歉出错了！状态码："+s); //出错时执行 s状态码
    }
});
```
*说明：1. crosssite这项设置是为了防止有人恶意把HASH修改成带有恶意代码的网站来欺骗用户窃取用户资料等等，请谨慎开启。2. onerror仅在AJAX请求后得到的HTTP状态码不等于200/301/302的时候生效。*

添加过后，页面内所有的A元素都被绑定了onclick属性，但href为"javascript:xxx"或"#"的、有"mdzz"属性的A元素会被排除，若要移除，最简单的只需要给A元素添加mdzz属性即可，如果要返回首页，可以给a的href设置为“#”，如下代码的A元素都会被排除：
```
<a href="#" mdzz onclick="alert(233)">2333</a>
<a href="javascript:alert(233)">xxx</a>
```
尽管href为#的元素被排除了，但是mdzz.JS是通过监控WINDOW.LOCATION.HASH是否改变来发送AJAX请求的，所以当某个A元素的href为#的时候，被点击时依旧能够正常执行代码以及发送Ajax请求。 给CLASS=CLASS的元素的所有子A元素屏蔽掉MDZZ
```
<div class="class">
    <a href="http://baidu.com">1</a>
    <a href="http://baidu.com">1</a>
    <a href="http://baidu.com">1</a>
    <a href="http://baidu.com">1</a>
</div>
<script>
_m = document.querySelectorAll('.class a');
for (var i = 0; i < _m.length; i++) {
    _m[i].setAttribute("mdzz","");
}
mdzz(true);
</script>
```
另外，如果你的页面添加了代码高亮的JS库什么的，改变URL HASH后高亮失效，这时候就要用complete重新执行高亮代码，如下
```
var mymdzz = new mdzz(true,{
    complete: function(){
        //AJAX请求完成时执行的代码
        highlightALL();
        alert('高亮完成！');
    }
});
```
当你重复点击同一个链接的时候，由于HASH没有发生改变，所以就不会发送请求，更不会刷新页面。 

为了实现同样的功能，原先A元素的href:#需要替换成javascript:void 0，否则页面会刷新，如下：
```
<a href="#" onclick="dosth();">Link</a>
<!-- 替换成下面这个 -->
<a href="javascript:void 0;" onclick="dosth();">Link</a>
<!-- 回到顶部 -->
<a href="javascript:window.scrollTo(0,0)">返回顶部</a>
译
```

<<<<<<< HEAD
END.
=======
END.
>>>>>>> origin/master
