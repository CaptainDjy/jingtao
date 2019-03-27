//算移动均线
function MovingAverage(data, i, n) {
    if (i >= n - 1) {
        var sum = 0;
        for (j = 0; j <= n - 1; j++) {
            sum = sum + parseFloat(data[i - j].ClosingPrice);
        }
        return sum / n;
    }
    else {
        return null;
    }
}

function GetStartTime(datetime) {
    var dt = new Date(datetime);
    dt.setHours(9);
    dt.setMinutes(0);
    dt.setSeconds(0);
    return dt.getTime();
}

function GetEndTime(datetime) {
    var dt = new Date(datetime);
    //20170706
    //dt.setHours(23);
    dt.setHours(20);
    dt.setMinutes(59);
    dt.setSeconds(0);
    return dt.getTime();
}

function Fill(data, summary, sysdatetime) {

    //从默认开始时间到当前服务器时间段的数据
    var result = [];


    if (data.length > 0) {
        //如果有分时数据
        var length = data.length
        for (i = 0; i < length; i++) {
            var currenttime = data[i].Time * 1000;
            var currentprice = data[i].Price;
            var currentvolume = data[i].Volume;
            var lasttime, lastprice;
            if (i == 0) //第一条数据
            {
                lasttime = GetStartTime(currenttime);
                lastprice = summary.OpenPrice;
                //第一条的时间大于默认开始时间 则补齐默认时间到开始时间的数据
                if (currenttime > GetStartTime(currenttime)) {
                    for (t = GetStartTime(currenttime) ; t < currenttime; t = t + 60000) {
                        result.push([t, lastprice, 0]);
                    }
                }
                result.push([currenttime, currentprice, currentvolume]);

            }
            else //从第二条数据开始
            {
                lasttime = data[i - 1].Time * 1000;
                lastprice = data[i - 1].Price;
                //如果时间差大于一分钟
                if (currenttime - lasttime > 60000) {
                    for (t = lasttime + 60000; t < currenttime; t = t + 60000) {
                        result.push([t, lastprice, 0]);
                    }
                }
                result.push([currenttime, currentprice, currentvolume]);
            }
        }

        var lastitem = result[result.length - 1];
        var lastitemtime = lastitem[0];
        var lastitemprice = lastitem[1];



        //补齐数据
        if (GetStartTime(lastitemtime) == GetStartTime(sysdatetime)) { //当天

            //如果当前时间小于收盘时间 补齐最后一条数据时间到当前时间的数据
			if(sysdatetime < GetEndTime(sysdatetime)) {
				for (t = lastitemtime + 60000; t <= sysdatetime ; t = t + 60000) {
					result.push([t, lastitemprice, 0]);
				}
			}
            //补齐当前时间到当天收盘时间的空数据 （让分时图时间轴显示的时间区间是整个交易时间）
            var len = result.length;
            for (t = result[len - 1][0] + 60000; t <= GetEndTime(sysdatetime) ; t = t + 60000) {
                result.push([t, null, null]);
            }
        } else {  //不是当天
            //补齐最后一条数据时间到收盘时间的数据
            for (t = lastitemtime + 60000; t <= GetEndTime(lastitemtime) ; t = t + 60000) {
                result.push([t, lastitemprice, 0]);
            }
        }

    } else {
        //如果没有分时数据
        if (isOpen) {   //开盘
            //如果当前时间小于收盘时间 开盘价到当前时间的数据
			if(sysdatetime < GetEndTime(sysdatetime)) {
				for (t = GetStartTime(sysdatetime) ; t <= sysdatetime; t = t + 60000) {
					result.push([t, summary.OpenPrice, 0]);
				}
			}
            //补齐当前时间到当天收盘时间的空数据（让分时图时间轴显示的时间区间是整个交易时间）
            var len = result.length;
            for (t = result[len - 1][0] + 60000; t <= GetEndTime(sysdatetime) ; t = t + 60000) {
                result.push([t, null, null]);
            }
        } else { //没开盘
            $("#loading").html("暂未开放交易").css("color", "#FF0000").css("font-size", "12px");;
        }
    }

    return result;
}



//获取数画图 K线
function Candle(result) {
    console.log(result)
    var summary = result.MarketInfo;
    var data = result.KlineList;

    if (data.length == 0) {
        $("#loading").html("暂未开放交易").css("color","#FF0000").css("font-size","12px");
    }else{
        //画图
        new highStockChart('kline', data);
    }
    /*$(".kline-ma").show();

    $.ajax({
        url: url,
        data: {code:code},
        type: "Post",
        dataType: 'json',
        success: function (result) {

            var summary = result.data.MarketInfo;

            var data = result.data.KLineList;

            if (data.length == 0) {
                $("#loading").html("暂未开放交易").css("color","#FF0000").css("font-size","12px");
            }else{
			//画图
			new highStockChart('kline', data);
            }

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            //alert("获取数据异常，请稍候再试！")
        }
    });*/

}

function Minute(result) {

    /* $(".kline-ma").hide();
     $.ajax({
         url: url,
         data:{code:code},
         type: "Post",
         dataType: 'json',
         success: function (result) {*/



            var summary = result.data.MarketInfo;
            console.log(summary,'123');
            var timeshare = result.data.TimeShare;
            var sysdatetime = result.data.SysDT * 1000;

            //分时区间
            //var fuckMin = summary.LimitDown;
            //var fuckMax = summary.LimitUp;




            //玫瑰花花的分时区间
            //if (code == "800008" || code == "800013") {
            //    var _fuck_ = 0;
            //    if (Math.abs(summary.HighestPrice - summary.OpenPrice) - Math.abs(summary.LowestPrice - summary.OpenPrice) > 0) {
            //        _fuck_ = Math.abs(summary.HighestPrice - summary.OpenPrice);
            //    } else {
            //        _fuck_ = Math.abs(summary.LowestPrice - summary.OpenPrice);
            //    }

            //    fuckMin = summary.OpenPrice - _fuck_;
            //    fuckMax = summary.OpenPrice + _fuck_;

            //    if (fuckMin < 0) {
            //        fuckMin = 0;
            //    }

            //}

            var _fuck_ = 0;
            //涨弧 跌弧
            if (Math.abs(summary.HighestPrice - summary.OpenPrice) - Math.abs(summary.LowestPrice - summary.OpenPrice) > 0) {
                _fuck_ = Math.abs(summary.HighestPrice - summary.OpenPrice);
            } else {
                _fuck_ = Math.abs(summary.LowestPrice - summary.OpenPrice);
            }

            if (_fuck_ == 0) {
                _fuck_ = summary.OpenPrice * 0.10;
            }

            var fuckMin = summary.OpenPrice - _fuck_;
            var fuckMax = summary.OpenPrice + _fuck_;

            if (fuckMin < 0) {
                fuckMin = 0;
            }


            var data = Fill(timeshare, summary, sysdatetime);

			Highcharts.theme = {
				xAxis: {
					gridLineColor: '#E7F2F8',
					gridLineWidth: 1,
				},
				global: {
					useUTC: false
				}

			};
			Highcharts.setOptions(Highcharts.theme);

			var prices = [];
            var volumes = [];



			for (i=0; i < data.length; i++) {
				prices.push([
					data[i][0], // the date
					data[i][1], // price

				]);

				volumes.push([
					data[i][0], // the date
					data[i][2] // the volume
				]);


			}



			// create the chart
			$('#kline').highcharts('StockChart', {
			    chart: {
			        plotBorderColor: '#E7F2F8',
			        plotBorderWidth: 0,

			    },

			    tooltip: {
			        formatter: function() {
			            if(this.y == undefined){
			                return;
			            }

			            var price = this.points[0].point.y.toFixed(2);
			            var volume = this.points[1].point.y;
			            var date = Highcharts.dateFormat('%Y-%m-%d', this.x);
			            var time = Highcharts.dateFormat('%H:%M', this.x);

			            if(volume > 10000*10000){
			                volume = (volume * 0.0001 * 0.0001).toFixed(2) + "亿";
			            } else if (volume > 10000) {
			                volume = (volume * 0.0001).toFixed(2) + "万";
			            }

			            var tip = '日期：' + date + '<br/>';
			            tip = tip +  '时间：'+ time + '<br/>';
			            tip = tip + '价格：' + price + '<br/>';
			            tip = tip + '成交：' + volume + '<br/>';

			            return tip;
			        }
			    },
			    credits: {
			        enabled: false
			    },
			    title: {
			        enabled: false
			    },
			    subtitle: {
			        enabled: false
			    },

			    yAxis: {
			        gapGridLineWidth: 1,
			        gridLineColor: '#E7F2F8',
			    },

			    exporting: {
			        enabled: false
			    },
			    scrollbar: {
			        enabled: false
			    },
			    navigator: {
			        enabled: false
			    },
			    rangeSelector: {
			        enabled: false
			    },

			        yAxis: [
						{

							height: '80%',
							lineWidth: 1,
							gapGridLineWidth: 1,
							gridLineColor: '#E7F2F8',
							//min: summary.LimitDown,
						    //max: summary.LimitUp,
						    min: fuckMin,
						    max: fuckMax,
						}, {

							top: '85%',
							height: '15%',
							offset: 0,
							lineWidth: 1,
							gapGridLineWidth: 1,
							gridLineColor: '#E7F2F8',
						}],
						series: [{
							type: 'area',
							name: 'prices',
							data: prices,
							lineWidth:0.8,
							gapSize: 5,
							tooltip: {
								valueDecimals: 4
							},
							fillColor : {
                                linearGradient : {
                                    x1: 0,
                                    y1: 0,
                                    x2: 0,
                                    y2: 1
                                },
                                stops : [
                                    [0, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')],
                                    [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0.3).get('rgba')]
                                ]
                            },

							dataGrouping:{
								enabled:false
							},
							animation: false

						}, {
							 gapSize: 5,
							type: 'column',
							name: 'Volume',
							data: volumes,
							yAxis: 1,
							color: '#7CB5EC',

							dataGrouping:{
								enabled:false
							},
							animation: false


						}]
					});




        /*},
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            //alert("获取数据异常，请稍候再试！")
        }
    });*/





}



var highStockChart = function (divid, data) {

    //开盘价^最高价^最低价^收盘价^成交量^成交额^涨跌幅^五日均线^十日均线^三十日均线
    var open, high, low, close, y, zde, zdf;
    //定义数组
    var ohlcArray = [], volumeArray = [], MA5Array = [], MA10Array = [], MA30Array = [], zdfArray = [], zdeArray = [];

    //修改colum条的颜色（重写了源码方法）
    var originalDrawPoints = Highcharts.seriesTypes.column.prototype.drawPoints;
    Highcharts.seriesTypes.column.prototype.drawPoints = function () {
        var merge = Highcharts.merge,
            series = this,
            chart = this.chart,
            points = series.points,
            i = points.length;

        while (i--) {
            var candlePoint = chart.series[0].points[i];
            if (candlePoint.open != undefined && candlePoint.close != undefined) {  //如果是K线图 改变矩形条颜色，否则不变

                var color = '#888888';
                if (candlePoint.open < candlePoint.close)
                {
                    color = '#DD2200';
                }
                else if (candlePoint.open > candlePoint.close)
                {
                    color = '#33AA11'
                }

                var seriesPointAttr = merge(series.pointAttr);
                seriesPointAttr[''].fill = color;
                seriesPointAttr.hover.fill = Highcharts.Color(color).brighten(0.3).get();
                seriesPointAttr.select.fill = color;
            } else {
                var seriesPointAttr = merge(series.pointAttr);
            }

            points[i].pointAttr = seriesPointAttr;
        }
        originalDrawPoints.call(this);
    }




    //修改candlestick条的颜色（重写了源码方法）
    var originalDrawPoints1 = Highcharts.seriesTypes.candlestick.prototype.drawPoints;
    Highcharts.seriesTypes.candlestick.prototype.drawPoints = function () {

        var merge = Highcharts.merge,
            series = this,
            chart = this.chart,
            points = series.points,
            i = points.length;

        while (i--) {
            var candlePoint = chart.series[0].points[i];
            if (candlePoint.open != undefined && candlePoint.close != undefined) {  //如果是K线图 改变矩形条颜色，否则不变
                var color = '#888888';
                if (candlePoint.open < candlePoint.close)
                {
                    color = '#DD2200';
                }
                else if (candlePoint.open > candlePoint.close)
                {
                    color = '#33AA11'
                }


                var seriesPointAttr = merge(series.pointAttr);
                seriesPointAttr[''].stroke = color;
                seriesPointAttr[''].fill = color;


            } else {
                var seriesPointAttr = merge(series.pointAttr);
            }

            points[i].pointAttr = seriesPointAttr;
        }
        originalDrawPoints1.call(this);
    }



    //常量本地化
    Highcharts.setOptions({
        global: {
            useUTC: false
        },
        lang: {
            rangeSelectorFrom: "日期:",
            rangeSelectorTo: "至",
            rangeSelectorZoom: "范围",
            loading: '加载中...',
            resetZoom:"重置缩放",
            resetZoomTitle:'重置缩放比例',
            shortMonths: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
            weekdays: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
        },
    });

    //把当前最新K线数据加载进来


    var length = data.length - 1;

    for (i = 0; i < data.length; i++) {
        ohlcArray.push([
			parseInt(data[i].Date * 1000), // the date
			parseFloat(data[i].OpeningPrice), // open
			parseFloat(data[i].HighestPrice), // high
			parseFloat(data[i].LowestPrice), // low
			parseFloat(data[i].ClosingPrice) // close
        ]);
        MA5Array.push([
				 parseInt(data[i].Date * 1000), // the date
                MovingAverage(data, i,5)
        ]);
        MA10Array.push([
	    	parseInt(data[i].Date * 1000),
	    	MovingAverage(data, i, 10)
        ]);

        MA30Array.push([
	             	parseInt(data[i].Date * 1000),
	                MovingAverage(data, i, 30)
        ]);
        volumeArray.push([
              parseInt(data[i].Date * 1000), // the date
              parseInt(data[i].Volume) // 成交量
        ]);
    }

    console.log(MA5Array);




    var last = data.length - 1;

    if (last + 1 <= 90 && $(window).width() > 768) {
        for (i = 1; i <= 90 - last - 1; i++) {
           var date = data[last].Date * 1000 + i * 24 * 60 * 60 * 1000;
           ohlcArray.push([
           date,    // the date
           null,    // open
           null,    // high
           null,    // low
           null     // close
           ]);
        }
    }

    //开始绘图
    return new Highcharts.StockChart({
        chart: {
            renderTo: divid,
            margin: [10, 10, 15, 10],
            plotBorderColor: '#E7F2F8',
            plotBorderWidth: 0,
            events: {
                load: function () {
                    var length = ohlcArray.length - 1;
                }
            }
        },
        loading: {
            labelStyle: {
                position: 'relative',
                top: '10em',
                zindex: 1000
            }
        },
        credits: {
            enabled: false,
        },
        rangeSelector: {
            //enabled: false,
            enabled: true,

            buttons: [{
                type: 'month',
                count: 1,
                text: '1m'
            }, {
                type: 'month',
                count: 3,
                text: '3m'
            },  {
                type: 'month',
                count: 6,
                text: '6m'
            },{
                type: 'all',
                text: 'All'
            }],
            selected: 1,
            height:0,
        },
        plotOptions: {
            //修改蜡烛颜色
            candlestick: {
                color: '#33AA11',
                upColor: '#DD2200',
                lineColor: '#33AA11',
                upLineColor: '#DD2200',
                maker: {
                    states: {
                        hover: {
                            enabled: false,
                        }
                    }
                },


            },
            //去掉曲线和蜡烛上的hover事件
            series: {
                states: {
                    hover: {
                        enabled: false
                    }
                },
                line: {
                    marker: {
                        enabled: false
                    }
                }
            }
        },
        //格式化悬浮框
        tooltip: {
            formatter: function () {
                if (this.y == undefined) {
                    return;
                }
                for (var i = 0; i < data.length; i++) {
                    if (this.x == data[i].Date * 1000) {
                        zde = parseFloat(data[i].ClosingPrice - data[i].OpeningPrice).toFixed(2);
                        //zdf = parseFloat((data[i].ClosingPrice - data[i].OpeningPrice) / data[i].OpeningPrice).toFixed(2);
						zdf = (data[i].ClosingPrice - data[i].OpeningPrice) / data[i].OpeningPrice;
                    }
                }
                open = parseFloat(this.points[0].point.open.toFixed(2));
                high = parseFloat(this.points[0].point.high.toFixed(2));
                low = parseFloat(this.points[0].point.low.toFixed(2));
                close = parseFloat(this.points[0].point.close.toFixed(2));
                y = this.points[1].point.y;
                var tip = '<b>' + Highcharts.dateFormat('%Y-%m-%d %A', this.x) + '</b><br/>';

                tip += '开盘价：' + open + '<br/>';
                tip += '收盘价：' + close + '<br/>';
                tip += '最高价：' + high + '<br/>';
                tip += '最低价：' + low + '<br/>';


                if (open < close)
                {
                    tip += '涨跌额：<span style="color:#F56363;">+' + zde + '</span><br/>';
                    tip += '涨跌幅：<span style="color:#F56363;">+' + (zdf * 100).toFixed(2) + '%</span><br/>';
                } else if(open>close) {
                    tip += '涨跌额：<span style="color:#5B910B;">' + zde + '</span><br/>';
                    tip += '涨跌幅：<span style="color:#5B910B;">' + (zdf * 100).toFixed(2) + '%</span><br/>';
                } else {
                    tip += '涨跌额：<span>' + zde + '</span><br/>';
                    tip += '涨跌幅：<span>' + (zdf * 100).toFixed(2) + '%</span><br/>';
                }


                if (y > 10000 * 10000) {
                    tip += "成交量：" + (y * 0.00000001).toFixed(2) + "亿<br/>";
                } else if (y > 10000) {
                    tip += "成交量：" + (y * 0.0001).toFixed(2) + "万<br/>";
                } else {
                    tip += "成交量：" + y + "<br/>";
                }





                return tip;
            },

            crosshairs: {
                //dashStyle: 'dash'
            },
            borderColor: 'white',

            shadow: true
        },
        title: {
            enabled: false
        },
        subtitle: {
            enabled: false
        },
        exporting: {
            enabled: false
        },
        scrollbar: {
            //enabled: false
        },
        navigator: {
            //enabled: false,
            adaptToUpdatedData: false,
            xAxis: {
                labels: {
                    formatter: function (e) {
                        return Highcharts.dateFormat('%m-%d', this.value);
                    }
                }
            },
            handles: {
                backgroundColor: '#808080',
            },
            // margin: 0
        },
        xAxis: {
            type: 'datetime',
            tickLength: 0,
            events: {
                afterSetExtremes: function (e) {
                    var minTime = Highcharts.dateFormat("%Y-%m-%d", e.min);
                    var maxTime = Highcharts.dateFormat("%Y-%m-%d", e.max);
                    var chart = this.chart;
                }
            },
            labels: {
                formatter: function (e) {
                    return Highcharts.dateFormat('%m-%d', this.value);
                    alert('a')
                }
            }
        },
        yAxis: [{
            title: {
                enable: false
            },
            height: '74%',
            lineWidth: 1,
            gridLineColor: '#346691',
            gridLineWidth: 0.1,
            opposite: true,
            min: 0
        }, {
            title: {
                enable: false
            },
            top: '80%',
            height: '20%',
            offset: 0,
            gridLineColor: '#346691',
            gridLineWidth: 0.1,
            lineWidth: 1,
        }],
        series: [
	    {
	        type: 'candlestick',
	        id: "candlestick",
	        name: "kline",
	        data: ohlcArray,
	        dataGrouping: {
	            enabled: false
	        },
	        animation: false,
	        //pointWidth: 5,
	    }
	    ,
        {
	        type: 'column',
	        name: '成交量',
	        data: volumeArray,
	        yAxis: 1,
	        dataGrouping: {
	            enabled: false
	        },
	        animation: false,
            //pointWidth: 5,
	    },
        {
	        type: 'spline',
	        name: 'MA5',
	        color: '#1aadce',
	        data: MA5Array,
	        lineWidth: 1,
	        dataGrouping: {
	            enabled: false
	        },
	        animation: false
	    },
        {
	        type: 'spline',
	        name: 'MA10',
	        data: MA10Array,
	        color: '#FF7F00',
	        threshold: null,
	        lineWidth: 1,
	        dataGrouping: {
	            enabled: false
	        },
	        animation: false
	    },
        {
	        type: 'spline',
	        name: 'MA30',
	        data: MA30Array,
	        color: '#910000',
	        threshold: null,
	        lineWidth: 1,
	        dataGrouping: {
	            enabled: false
	        },
	        animation: false
	    }
        ]
    });
}

