My Daft Zombie  Zoo . js (MDZZ.js)
------
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
