(function($) {
	$.fn.lcnK4PieNumber = "";  //许可
	$.fn.brand4PieNumber = ""; // 交易品牌
	$.fn.HangQingBaseUrl = "http://k.tanjiaoyi.com:8080/";
// 	$.fn.HangQingBaseUrl = "http://127.0.0.1:8080/priceshow/";

	HangQingBaseUrl = $.fn.HangQingBaseUrl;
	var hasLoadStockFile = false;
	var isLoadFlag = false;

	/*$.fn.hqPie4Number = function(lck,brand) {
		var containerDiv = $(this)[0].id;
		$.getScript(HangQingBaseUrl + 'js/hq/hqscriptPie4Number.js', function() {
			loadCharFile($.fn.hqPie4Number1, containerDiv, lck, brand);
		});
	}*/

	/*$.fn.hqPie4Money = function(lck,brand) {
		var containerDiv = $(this)[0].id;
		$.getScript(HangQingBaseUrl + 'js/hq/hqscriptPie4Money.js', function() {
			loadCharFile($.fn.hqPie4Money1, containerDiv, lck, brand);
		});
	}*/

	$.fn.wholeCountryChart = function(lck,brand) {
		var containerDiv = $(this)[0].id;
		$.getScript("../static/mobile/js/lib/index_tan/hqscriptWholeCountry.js", function() {
			loadCharFile($.fn.createChart1, containerDiv, lck, brand);
		});
	}

	/*$.fn.hqChart4Phone = function(lck,brand) {
		var containerDiv = $(this)[0].id;
		$.getScript(HangQingBaseUrl + 'js/hq/hqscript4kPhone.js', function() {
			loadCharFile($.fn.hqChart4Phone1, containerDiv, lck,brand);
		});
	}*/

	/*$.fn.hqRiseGrid = function(lck,brand) {
		var containerDiv = $(this)[0].id;
		$.getScript(HangQingBaseUrl + 'js/hq/hqscript4Risegrid.js', function() {
			loadCharFile($.fn.hqRiseGrid1, containerDiv, lck,brand);
		});
	}*/

	/*$.fn.hqGrid = function(lck,brand) {
		var containerDiv = $(this)[0].id;
		$.getScript(HangQingBaseUrl + 'js/hq/hqscript4grid.js', function() {
			$.fn.hqGrid1(containerDiv, lck,brand||"TAN");
		});
	}*/

	/*$.fn.hqSumGrid = function(lck,brand) {
		var containerDiv = $(this)[0].id;
		$.getScript(HangQingBaseUrl + 'js/hq/hqscript4Sumgrid.js', function() {
			$.fn.hqSumGrid1(containerDiv, lck,brand||"TAN");
		});
	}*/

	$.fn.hqColumn = function(lck,brand) {
		var containerDiv = $(this)[0].id;
		$.getScript(HangQingBaseUrl + 'js/hq/hqscript4Column.js', function() {
			loadCharFile($.fn.hqColumn1, containerDiv, lck,brand);
		});
	}

	var t=0; //时间
	var time = 1000;
	function loadCharFile(callBack, param, lck, brand) {
		brand = brand || "TAN";
		var div = '#' + param;
		var loadingDiv = param + 'Load';
		var loadingId = '#' + loadingDiv;
		if($('#loadingDiv').length == 0) {
			var urlT = HangQingBaseUrl + 'images/loading.gif';
			$('<div id="' + loadingDiv + '" style="width:30px; height:0px; position: relative; margin-top:-10px; float:left; left:100px; top:100px; z-index:30" ><image src= ' + urlT + '></image></div>').appendTo($(div));
			$(loadingId).css("left", $(div).width() / 2 -30 + "px");
			$(loadingId).css("top", $(div).height() / 2 - 100 + "px");
		}
		$(loadingId).show();

		$.fn.hqlck = lck;
		$.fn.hqbrand = brand;
		if(hasLoadStockFile) {
			time += 200;
			//clearTimeout(t);
			t=setTimeout(function() {  //延时加载

				callBack(param, lck, brand);

			}, time);
		} else {
			var scriptArry = $('script');

			$(scriptArry).each(function(index, item) {
				var tt = item;
				var src = $(tt).attr('src');
				if(src) {
					var ttIndex = src.indexOf('js/exporting.js');
					if(ttIndex >= 0) {
						hasLoadStockFile = true;
						return false;
					}
				}
			});
			if(! hasLoadStockFile) {
				hasLoadStockFile = true;
				loadjscssfile(HangQingBaseUrl + 'js/highstock.js', 'js', function() {
					loadjscssfile(HangQingBaseUrl + 'js/exporting.js', 'js', function() {
						callBack(param, lck, brand);
					});
				});
			}
		}
	}

	function loadjscssfile(filename, filetype,onLoadFn){
		var head = document.head || document.getElementsByTagName('head')[0];
		if (filetype=="js"){ // 判断文件类型
			var script = document.createElement('script');
		    script.type = 'text/javascript';
		    script.src = filename;
		    if(onLoadFn != null && onLoadFn != undefined) {
			    script.onload = onLoadFn;
		        script.onreadystatechange = function() {
		            if (this.readyState == 'loaded' || this.readyState == 'complete') {
		                onLoadFn();
		            }
		        };
		    }
		    head.appendChild(script);
		} else if (filetype=="css"){ // 判断文件类型
			var link = document.createElement('link');
			link.rel="stylesheet";
			link.type="text/css";
			link.href=filename;
			head.appendChild(link);
		}
	}

    //版权设置
	$.fn.getCredit = function () {
		var tt= {
		    	enabled : false,
		    	text: 'tanjiaoyi.com',
		    	href : "http://tanjiaoyi.com",
		    	target : 'newWindow', //self, newWindow
		    	style: {
		    		'margin-top' : '5px',
		    		'padding-top' : '8px',
		    		cursor: 'pointer',
		    		color: '#909090',
		    		fontSize: '14px',
		    		'font-style': 'italic',
		    		'font-weight' : 'bold',
		    		'font-family' : 'Microsoft Yahei'

		    	}
		};
		 return tt;
	}

})(jQuery);

