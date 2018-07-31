var hashpage = {
	create:function(o){
		var oo = [], i = 0;
		for (var hv in o){
			oo[i] = hv;
			i++;
		}
		var ls = function(){
			for (var i = 0; i < oo.length; i++) {
				if (window.location.hash == "#"+oo[i]) {
					var htmptf = typeof o[oo[i]];
					if (htmptf == "function") {
						o[oo[i]]();
					}else if (htmptf == "string" && typeof o.dom == "object") {
						//输出到dom
						o.dom.innerHTML = o[oo[i]];
					};
				}
			}
		}
		window.onhashchange = ls;
		if (window.location.hash !== "") {
			ls();
		}
	}//create
}