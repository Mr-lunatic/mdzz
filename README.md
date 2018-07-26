My Daft Zombie  Zoo . js (MDZZ.js)
------
*名字是瞎几把拼凑的...*

真实滑天下之大稽，没有任何一个JS库比这个更加智障了！
我是一款鸡肋的模仿PJAX和网易云音乐网页版的JS库
可以通过监控window.location.hash的变化来无刷新改变网页内容
0.0.1版本仅3.33KB的大小 (๑•̀ㅂ•́)و✧
共计：98个汉字 3227个字符 包含：72个汉字 2个全角标点 3053个字母 26个数字
作为一个有尊严的JS库，我内心很有逼数，甚至还支持IE8！！！
如果你不知道我可以用来干什么的话，我也只有嘤嘤嘤了 (┬＿┬)。

完整教程请看：https://mdzz.lljh.bid/#/page/p1.html

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
