


//算移动均线
function MovingAverage(data, i, n) {
    if (i >= n - 1) {
        var sum = 0;
        for (var j = 0; j <= n - 1; j++) {
            sum = sum + parseFloat(data[i - j][4]);
        }
        return sum / n;
    }
    else {
        return null;
    }
}

function Candle(id,result) {
    //画图
    new highStockChart(id, result);
}

//分时
function Minute(id,result) {
    // console.log(result);
    var data=result;
    var prices = [];
    var volumes = [];
    for (var i=0; i < data.length; i++) {
        prices.push([
            data[i].time*1000, // the date
            data[i].price*1, // price
        ]);

        volumes.push([
            data[i].time*1000, // the date
            data[i].num // the volume
        ]);
    }
    //分时区域图
    $('#'+id).highcharts('StockChart', {
        chart: {
            //绘图区域
            plotBorderColor: '#E7F2F8',
            plotBorderWidth: 0,
        },
        tooltip: {
            formatter: function() {
                if(this.y == undefined){
                    return;
                }
                var price = this.points[0].point.y;//.toFixed(2)
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
        yAxis: [
            {
                height: '80%',
                lineWidth: 1,
                gapGridLineWidth: 1,
                gridLineColor: '#E7F2F8',
                //min: summary.LimitDown,
                //max: summary.LimitUp,
                // min: fuckMin,
                // max: fuckMax,
            }, {
                top: '85%',
                height: '15%',
                offset: 0,
                lineWidth: 1,
                gapGridLineWidth: 1,
                gridLineColor: '#E7F2F8',
            }],
        series: [{
            type: 'spline',
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

}
//分钟
function moduleTime(id,type,objMinData) {
    var typeLine;//图标类型
    var dataTimeType;//数据上一次获取时间
    var data1=[],data2=[];
    //替换成传递的type类型
    switch (type){
        case "5m":
            typeLine=1;
            var temp=objMinData['fiveM'];
            if(temp.length!=0){
                dataTimeType=temp[0][temp[0].length-1][0];
            }
            break;
        case "30m":
            typeLine=2;
            var temp=objMinData['thirtyM'];
            if(temp.length!=0){
                dataTimeType=temp[0][temp[0].length-1][0];
            }
            break;
        case "1h":
            typeLine=3;
            var temp=objMinData['oneHour'];
            if(temp.length!=0){
                dataTimeType=temp[0][temp[0].length-1][0];
            }
            break;
        case "1M":
            typeLine=4;
            var temp=objMinData['oneMonth'];
            if(temp.length!=0){
                dataTimeType=temp[0][temp[0].length-1][0];
            }
            break;
    };
   /* function afterSetExtremes(e) {
        var chart = $('#'+id).highcharts();
        chart.showLoading('数据加载中');
        /!*$.getJSON('https://www.highcharts.com/samples/data/from-sql.php?start=' + Math.round(e.min) +
            '&end=' + Math.round(e.max) + '&callback=?', function (data) {
            chart.series[0].setData(data);
            chart.hideLoading();
        });*!/
        $.ajax({
            url:"/trade/market",
            type:"get",
            data:{start:Math.round(e.min/1000),end:Math.round(e.max/1000),type:type},
            success:function (res) {
                // console.log(res);
                dataGroup(res.data);
                return;
                chart.series[0].setData(data);
                chart.hideLoading();
            }

        });
    };*/
    // See source code from the JSONP handler at https://github.com/highslide-software/highcharts.com/blob/master/samples/data/from-sql.php
    dataTimeType=dataTimeType?dataTimeType/1000:'';//匹配后台时间戳
    console.log(dataTimeType);
    //获取数据
    $.ajax({
        url:"/trade/market",
        type:"get",
        data:{type:typeLine,start:dataTimeType},
        success:function (res) {
            // console.log(res);
            dataGroup(res.data);
            // chart.hideLoading();
            //chart.series[0].setData(data);
        }

    });

    //数据处理
    function dataGroup(data) {
        for(var i=0;i<data.length;i++){
            data1.push([
                data[i].t*1000,//时间
                data[i].o*1,//开盘价
                data[i].h*1,//时间
                data[i].l*1,//时间
                data[i].c*1,//时间
            ]);
            data2.push([
                data[i].t*1000,//时间
                data[i].n*1,//数量
            ]);
        }
        //存储数据合并最新数据
        switch (type){
            case "5m":
                dataGRoupFun("fiveM",setMinData);
                break;
            case "30m":
                dataGRoupFun("thirtyM",setMinData);
                break;
            case "1h":
                dataGRoupFun("oneHour",setMinData);
                break;
            case "1M":
                dataGRoupFun("oneMonth",setMinData);
                break;
        };



    }
    //数据合并函数
    function dataGRoupFun(val,callBack) {
        var dataObj=objMinData[val];
        if(dataObj.length!=0){
            data1=dataObj[0].concat(data1);
            data2=dataObj[1].concat(data2);
            objMinData[val]=[data1,data2];
        }else{
            objMinData[val]=[data1,data2];
        }
        // console.log(objMinData[val].length);
        callBack(data1,data2);
    }
    //画图
    function  setMinData(data1,data2) {
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
        // create the chart
        $('#'+id).highcharts('StockChart', {
            chart : {
                type: 'candlestick',
                zoomType: 'x'
            },
            credits:{
                enabled: false
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
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            rangeSelector : {
                enabled: false,
                buttons: [{
                    type: 'hour',
                    count: 1,
                    text: '1h'
                }, {
                    type: 'day',
                    count: 1,
                    text: '1d'
                }, {
                    type: 'month',
                    count: 1,
                    text: '1m'
                }, {
                    type: 'year',
                    count: 1,
                    text: '1y'
                }, {
                    type: 'all',
                    text: 'All'
                }],
                inputEnabled: true, // it supports only days
                selected : 4 // all
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
            tooltip: {
                formatter: function () {
                    if (this.y == undefined) {
                        return;
                    }
                    // console.log(this);
                    /*计算涨跌额
                    for (var i = 0; i < data.length; i++) {
                        if (this.x == data[i].Date * 1000) {
                            zde = parseFloat(data[i].ClosingPrice - data[i].OpeningPrice).toFixed(2);
                            //zdf = parseFloat((data[i].ClosingPrice - data[i].OpeningPrice) / data[i].OpeningPrice).toFixed(2);
                            zdf = (data[i].ClosingPrice - data[i].OpeningPrice) / data[i].OpeningPrice;
                        }
                    }*/
                    open = this.points[0].point.open || 0;
                    high = this.points[0].point.high || 0;
                    low = this.points[0].point.low || 0;
                    close = this.points[0].point.close || 0;
                    y = this.points[0].point.y || 0;

                    var tip = '<b>' + Highcharts.dateFormat('%Y-%m-%d %H:%M:%S %A', this.x) + '</b><br/>';
                    tip += '开盘价：' + open + '<br/>';
                    tip += '收盘价：' + close + '<br/>';
                    tip += '最高价：' + high + '<br/>';
                    tip += '最低价：' + low + '<br/>';

                    /*if (open < close)
                    {
                        tip += '涨跌额：<span style="color:#F56363;">+' + zde + '</span><br/>';
                        tip += '涨跌幅：<span style="color:#F56363;">+' + (zdf * 100).toFixed(2) + '%</span><br/>';
                    } else if(open>close) {
                        tip += '涨跌额：<span style="color:#5B910B;">' + zde + '</span><br/>';
                        tip += '涨跌幅：<span style="color:#5B910B;">' + (zdf * 100).toFixed(2) + '%</span><br/>';
                    } else {
                        tip += '涨跌额：<span>' + zde + '</span><br/>';
                        tip += '涨跌幅：<span>' + (zdf * 100).toFixed(2) + '%</span><br/>';
                    }*/

                    if (y > 10000 * 10000) {
                        tip += "成交量：" + (y * 0.00000001).toFixed(2) + "亿<br/>";
                    } else if (y > 10000) {
                        tip += "成交量：" + (y * 0.0001).toFixed(2) + "万<br/>";
                    } else {
                        tip += "成交量：" + y.toFixed(2) + "<br/>";
                    }
                    // tip += "成交量：" + y.toFixed(2) + "<br/>";
                    return tip;
                },
                crosshairs: {
                    dashStyle: 'dash'
                },
                borderColor: 'white',
                valueDecimals: 2,
                shadow: true
            },
            xAxis : {
                type: 'datetime',
                tickLength: 1,
                events : {
                    // afterSetExtremes : afterSetExtremes
                },
                // minRange: 3600 * 1000 ,// one hour，
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
            series : [{
                data : data1,
                type: 'candlestick',
                name: 'kline',
                color: 'green',
                lineColor: 'green',
                upColor: 'red',
                upLineColor: 'red',
                dataGrouping: {
                    // units: groupingUnits
                }
            },{
                type: 'column',
                name: '成交量',
                data: data2,
                yAxis: 1,
                dataGrouping: {
                    // units: groupingUnits
                }
            },]
        });
    }

}

//创建图标
var highStockChart = function (divid, data) {
    //开盘价^最高价^最低价^收盘价^成交量^成交额^涨跌幅^五日均线^十日均线^三十日均线
    var open, high, low, close, y, zde, zdf;
    //定义数组
    var ohlcArray = [], volumeArray = [], MA5Array = [], MA10Array = [], MA30Array = [];

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
    var dataLength = data.length;
    for (var i=0; i < dataLength; i++) {
        ohlcArray.push([
            data[i][0], // the date
            data[i][1], // open
            data[i][2], // high
            data[i][3], // low
            data[i][4] // close
        ]);
        MA5Array.push([
            parseInt(data[i][0]), // the date
            MovingAverage(data, i,5)
        ]);
        MA10Array.push([
            parseInt(data[i][0]), // the date
            MovingAverage(data, i, 10)
        ]);
        MA30Array.push([
            parseInt(data[i][0]), // the date
            MovingAverage(data, i, 20)
        ]);
        volumeArray.push([
            data[i][0], // the date
            data[i][5] // the volume
        ]);
    }
    $('#'+divid).highcharts('StockChart', {
        chart:{
            zoomType: "x",
            margin: [30, 10, 15, 10],
            plotBorderColor: '#E7F2F8',
            plotBorderWidth: 0,
            events: {
            }
        },
        credits: {
            enabled: false,
        },
        rangeSelector: {
            enabled:false,
            selected: 5,
            inputDateFormat: '%Y-%m-%d'
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
        tooltip: {
            formatter: function () {
                if (this.y == undefined) {
                    return;
                }
                // console.log(this);
                /*
                计算涨跌额
                for (var i = 0; i < data.length; i++) {
                    if (this.x == data[i].Date * 1000) {
                        zde = parseFloat(data[i].ClosingPrice - data[i].OpeningPrice).toFixed(2);
                        //zdf = parseFloat((data[i].ClosingPrice - data[i].OpeningPrice) / data[i].OpeningPrice).toFixed(2);
                        zdf = (data[i].ClosingPrice - data[i].OpeningPrice) / data[i].OpeningPrice;
                    }
                }*/
                open = this.points[0].point.open || 0;
                high = this.points[0].point.high || 0;
                low = this.points[0].point.low || 0;
                close = this.points[0].point.close || 0;
                y = this.points[1].point.y || 0;
                var tip = '<b>' + Highcharts.dateFormat('%Y-%m-%d %A', this.x) + '</b><br/>';

                tip += '开盘价：' + open + '<br/>';
                tip += '收盘价：' + close + '<br/>';
                tip += '最高价：' + high + '<br/>';
                tip += '最低价：' + low + '<br/>';


                /*if (open < close)
                {
                    tip += '涨跌额：<span style="color:#F56363;">+' + zde + '</span><br/>';
                    tip += '涨跌幅：<span style="color:#F56363;">+' + (zdf * 100).toFixed(2) + '%</span><br/>';
                } else if(open>close) {
                    tip += '涨跌额：<span style="color:#5B910B;">' + zde + '</span><br/>';
                    tip += '涨跌幅：<span style="color:#5B910B;">' + (zdf * 100).toFixed(2) + '%</span><br/>';
                } else {
                    tip += '涨跌额：<span>' + zde + '</span><br/>';
                    tip += '涨跌幅：<span>' + (zdf * 100).toFixed(2) + '%</span><br/>';
                }*/

                if (y > 10000 * 10000) {
                    tip += "成交量：" + (y * 0.00000001).toFixed(2) + "亿<br/>";
                } else if (y > 10000) {
                    tip += "成交量：" + (y * 0.0001).toFixed(2) + "万<br/>";
                } else {
                    tip += "成交量：" + y.toFixed(2) + "<br/>";
                }
                // tip += "成交量：" + y.toFixed(2) + "<br/>";
                return tip;
            },

            crosshairs: {
                dashStyle: 'dash'
            },
            borderColor: 'white',
            valueDecimals: 2,
            shadow: true
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
        series: [{
            type: 'candlestick',
            name: 'kline',
            color: 'green',
            lineColor: 'green',
            upColor: 'red',
            upLineColor: 'red',
            data: ohlcArray,
            dataGrouping: {
                // units: groupingUnits
            }
        }, {
            type: 'column',
            name: '成交量',
            data: volumeArray,
            yAxis: 1,
            dataGrouping: {
                // units: groupingUnits
            }
        },{
            type: 'spline',
            name: 'MA5',
            color: '#1aadce',
            data: MA5Array,
            lineWidth: 1,
            dataGrouping: {
                enabled: false
            },
            animation: false
        },{
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
            }]
    });

}
