(function($){HangQingBaseUrl=$.fn.HangQingBaseUrl;var divObj;var lrc;var brd;var divContainer;$.fn.createChart1=function(containerDiv,lrcT,brand){divContainer=containerDiv;lrc=lrcT;brd=brand;divObj=$('#'+containerDiv);loadScript(getData);};function loadScript(callBackFunction){callBackFunction()}function getData(){$.getJSON(HangQingBaseUrl+"KDataController/getHouseDatasInAverage.do?jsoncallback=?&lcnK="+lrc+"&brand="+brd,function(data){processData(data)})}var groupingUnits=[['week',[1]],['month',[1,2,3,4,6]]];var yAxiss=[{top:'2%',labels:{align:'left',x:-5,format:'{value}元'},title:{text:'成交价(元)',rotation:0,align:'high',margin:-59,x:0,y:-5,style:{"font-size":"13px","color":"#707070","fontWeight":"bold","font-family":"Microsoft Yahei","margin-bottom":"15px","padding-bottom":"10px"}},height:'100%',lineWidth:1,opposite:false}];var seriesOptions=[];function processData(data){Highcharts.theme={colors:["#2b908f","#90ee7e","#f45b5b","#7798BF","#aaeeee","#ff0066","#eeaaee","#55BF3B","#DF5353","#7798BF","#aaeeee"],chart:{backgroundColor:{linearGradient:{x1:0,y1:0,x2:1,y2:1},stops:[[0,'#2a2a2b'],[1,'#3e3e40']]},style:{fontFamily:"'Unica One', sans-serif"},plotBorderColor:'#606063'},title:{style:{color:'#E0E0E3',textTransform:'uppercase',fontSize:'20px'}},subtitle:{style:{color:'#E0E0E3',textTransform:'uppercase'}},xAxis:{gridLineColor:'#707073',labels:{style:{color:'#E0E0E3'}},lineColor:'#707073',minorGridLineColor:'#505053',tickColor:'#707073',title:{style:{color:'#A0A0A3'}}},yAxis:{gridLineColor:'#707073',labels:{style:{color:'#E0E0E3'}},lineColor:'#707073',minorGridLineColor:'#505053',tickColor:'#707073',tickWidth:1,title:{style:{color:'#A0A0A3'}}},tooltip:{},plotOptions:{series:{dataLabels:{color:'#B0B0B3'},lineWidth:1,marker:{lineColor:'#333'}},boxplot:{fillColor:'#505053'},candlestick:{lineColor:'white'},errorbar:{color:'white'}},legend:{itemStyle:{color:'#E0E0E3'},itemHoverStyle:{color:'#FFF'},itemHiddenStyle:{color:'#606063'}},credits:{style:{color:'#666'}},labels:{style:{color:'#707073'}},drilldown:{activeAxisLabelStyle:{color:'#F0F0F3'},activeDataLabelStyle:{color:'#F0F0F3'}},navigation:{buttonOptions:{symbolStroke:'#DDDDDD',theme:{fill:'#505053'}}},rangeSelector:{enabled:false,buttonTheme:{fill:'#505053',stroke:'#000000',style:{color:'#CCC'},states:{hover:{fill:'#707073',stroke:'#000000',style:{color:'white'}},select:{fill:'#000003',stroke:'#000000',style:{color:'white'}}}},inputBoxBorderColor:'#505053',inputStyle:{backgroundColor:'#333',color:'silver'},labelStyle:{color:'silver'}},navigator:{handles:{backgroundColor:'#666',borderColor:'#AAA'},outlineColor:'#CCC',maskFill:'rgba(255,255,255,0.1)',series:{color:'#7798BF',lineColor:'#A6C7ED'},xAxis:{gridLineColor:'#505053'}},scrollbar:{barBackgroundColor:'#808083',barBorderColor:'#808083',buttonArrowColor:'#CCC',buttonBackgroundColor:'#606063',buttonBorderColor:'#606063',rifleColor:'#FFF',trackBackgroundColor:'#404043',trackBorderColor:'#404043'},legendBackgroundColor:'rgba(0, 0, 0, 0.5)',background2:'#505053',dataLabelsColor:'#B0B0B3',textColor:'#C0C0C0',contrastTextColor:'#F0F0F3',maskColor:'rgba(255,255,255,0.3)'};Highcharts.setOptions(Highcharts.theme);Highcharts.setOptions({lang:{months:['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],weekdays:['星期一','星期二','星期三','星期四','星期五','星期六','星期日'],shortMonths:['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],rangeSelectorFrom:'开始',rangeSelectorTo:'截至',rangeSelectorZoom:''}});var sortHouses=[];$.each(data,function(key,value){if(value&&value.length>0){var first=value[0];sortHouses.push(first)}});sortHouses.sort(function(a,b){var begin=new Date((a.INDATE).replace(/-/g,"/"));var end=new Date((b.INDATE).replace(/-/g,"/"));return begin>end});var i=0;$(sortHouses).each(function(index,item){var chartData=data[item.HOUSENAME];var houseName=item.HOUSENAME;var dataTemp=[];$.each(chartData,function(j,tt){var dateT=tt.INDATE.split('-');dataTemp=$.merge(dataTemp,[{x:Date.UTC(parseInt(dateT[0],10),parseInt(dateT[1],10)-1,parseInt(dateT[2],10)),y:(tt.deal/tt.houseCnt),dealamount:(tt.DEALAMOUNT/tt.houseCnt),dealnum:(tt.DEALNUM/tt.houseCnt)}])});seriesOptions[i]={type:'spline',name:houseName,data:dataTemp,yAxis:0,dataGrouping:{units:groupingUnits}};i++;});initChart();var loadingDivId='#'+divContainer+'Load';$(loadingDivId).hide()}function initChart(){var chart=$(divObj).highcharts('StockChart',{legend:getLegend(),chart:{type:'spline'},credits:$.fn.getCredit(),plotOptions:{spline:{marker:{symbol:'circle',radius:4,lineWidth:1}}},navigator:{enabled:false},tooltip:{shared:true,useHTML:true,shadow:false,formatter:function(){var ss='';ss+='<span style="font-size: 14px; font-family:Microsoft Yahei; color:#009933"><b>'+Highcharts.dateFormat('%Y年%b%e日  %A',this.x)+'</b></span><br/>';ss+='<ul style="-webkit-padding-start: 5px;margin: 10px 0 0 -4px;padding:0;">';var addrLenth=this.points.length;$.each(this.points,function(i,point){var dealData=this.point.dealnum?{a:this.point.y,b:this.point.dealnum}:getDealData(this.series.options.data,this.x);if(!dealData)return true;ss+='<li style="list-style-type:none;">';ss+='<span style="font-size:17px;color:'+this.series.color+';">■</span>';ss+='<b>'+this.series.name+'</b> ';ss+=' 成交价:'+Highcharts.numberFormat(dealData.a,2)+'元';ss+=' 成交量:'+Highcharts.numberFormat(dealData.b,2)+'吨</li>'});ss+='</ul>';return ss},valueDecimals:2,style:{fontSize:'13px',padding:'8px',width:'300px','font-family':'Microsoft Yahei'},borderColor:'#eee'},series:seriesOptions,yAxis:yAxiss,rangeSelector:{inputEnabled:false,selected:5,buttonTheme:{states:{select:{fill:'#009933',style:{color:'white'}}}},buttons:[{type:'day',count:10,text:'10天'},{type:'month',count:1,text:'1月'},{type:'month',count:3,text:'3月'},{type:'month',count:6,text:'6月'},{type:'year',count:1,text:'年'},{type:'all',count:1,text:'全部'}]},xAxis:{dateTimeLabelFormats:{second:'%m月%d日%H:%M:%S',minute:'%m月%d日%H:%M',hour:'%m月%d日%H:%M',day:'%m月%d日',week:'%m月%d日',month:'%Y年%m月',year:'%Y年'}},navigation:{buttonOptions:{enabled:false}},exporting:{enabled:true}})}function getDealData(datas,x){if(!datas||datas.length<=0)return null;var startIndex=0,endIndex=datas.length-1,middle=Math.floor((endIndex-startIndex)/2);while(datas[middle].x!==x&&startIndex<endIndex){if(datas[middle].x>x){endIndex=middle-1}else if(datas[middle].x<x){startIndex=middle+1}middle=Math.floor((endIndex+startIndex)/2)}return{a:datas[middle].y,b:datas[middle].dealnum}}function getLegend(){var tt={enabled:true,align:'center',backgroundColor:'#555',borderColor:'black',borderWidth:0,layout:'horizontal',verticalAlign:'bottom',itemDistance:20,shadow:false};return tt}})(jQuery);
