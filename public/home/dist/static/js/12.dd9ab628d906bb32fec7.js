webpackJsonp([12],{"9aeQ":function(t,i){},Q2J2:function(t,i,o){"use strict";Object.defineProperty(i,"__esModule",{value:!0});var e={name:"chanelList2",data:function(){return{active:0,value:"",list:[],nav:[],loading:!1,finished:!1,pageNum:1,pageSize:10,index:0,isReload:!1,myload:!1,keyWord:"",toUrl:"",toToken:""}},created:function(){this.toUrl=this.$route.query.id,this.toToken=this.$route.query.token,this.findComType()},methods:{onCopy:function(t){this.$toast("复制成功")},onError:function(){this.$toast("复制失败")},onSearch:function(){alert(this.value)},onLoad:function(){this.loading=!0,this.pageNum++},findComType:function(){var t=this;this.$POST("/goods/category").then(function(i){200==i.code&&(i.data.length>0&&(t.nav=i.data,t.nav.splice(15,1),t.keyWord=i.data[0].name),t.getListById())}).catch(function(t){console.log(t)})},onChangeTab:function(t,i){var o=this;this.list=[],this.pageNum=1,this.keyWord=t+1;var e={page:this.pageNum,cid:this.keyWord,alimm_pid:this.toToken,sort:0,haveNoToken:!0};this.$POST(this.toUrl,e,!1).then(function(t){console.log(t),200===t.code?t.data.length>0?(o.list=t.data,t.data.length<20&&(o.finished=!0,o.$toast("已经到底了！")),o.loading=!1):(o.finished=!0,o.$toast("没有数据了亲!")):o.finished=!0,o.loading=!1,console.log("加载数据成功!")}).catch(function(t){o.loading=!1,o.finished=!0,o.pageNum--,o.isReload=!0,console.log("获取数据失败!")})},getListById:function(){var t=this,i={haveNoToken:!0,page:this.pageNum,alimm_pid:this.toToken,cid:1,sort:0};this.$POST(this.toUrl,i,!1).then(function(i){console.log(i),200===i.code?i.data.length>0?(t.list=i.data,i.data.length<20&&(t.finished=!0,t.$toast("已经到底了！")),t.loading=!1):(t.finished=!0,t.$toast("没有数据了亲!")):t.finished=!0,t.loading=!1,console.log("加载数据成功!")}).catch(function(i){t.loading=!1,t.finished=!0,t.pageNum--,t.isReload=!0,console.log("获取数据失败!")})},reload:function(){this.finished=!1,this.isReload=!1},load:function(){this.findComType(),this.myload=!1}}},a={render:function(){var t=this,i=t.$createElement,o=t._self._c||i;return o("div",{staticClass:"flex-page",attrs:{id:"chanelList2"}},[o("div",{staticClass:"page-header"},[o("van-nav-bar",[o("div",{attrs:{slot:"title"},slot:"title"},[t._v("\n        商品列表\n      ")]),t._v(" "),o("van-icon",{attrs:{slot:"left",name:"arrow-left",color:"black"},on:{click:function(i){t.$router.go(-1)}},slot:"left"})],1)],1),t._v(" "),o("div",{staticClass:"page-body"},[o("div",{staticClass:"bg"}),t._v(" "),t.myload?o("van-button",{staticClass:"mybtn",attrs:{size:"large",square:"",type:"primary"},on:{click:t.load}},[t._v("重新加载")]):t._e(),t._v(" "),o("van-tabs",{attrs:{sticky:""},on:{click:t.onChangeTab},model:{value:t.active,callback:function(i){t.active=i},expression:"active"}},t._l(t.nav,function(i,e){return o("van-tab",{key:e,attrs:{title:i.title}},[o("van-list",{attrs:{offset:100},on:{load:t.onLoad}},[o("div",{staticClass:"list"},t._l(t.list,function(i,e){return o("div",{key:e,staticClass:"good-list",on:{click:function(o){t.$router.push({path:"/goodsDetail",query:{coupon_links:i.coupon_link,commission_moneys:i.commission_money,images:i.thumb,yishou:i.sales_num,id:i.origin_id,delprice:i.origin_price,title:i.title,nowPrice:i.coupon_price,quanJine:i.coupon_money,thumbs:i.thumb}})}}},[o("img",{directives:[{name:"lazy",rawName:"v-lazy",value:i.thumb,expression:"i.thumb"}],staticStyle:{width:"150px",height:"140px"},attrs:{src:i.thumb}}),t._v(" "),o("div",[o("span",{staticClass:"searchlist-goodsname",staticStyle:{overflow:"hidden","text-overflow":"ellipsis",display:"-webkit-box","-webkit-line-clamp":"2","-webkit-box-orient":"vertical"}},[t._v(t._s(i.title))]),t._v(" "),o("div",[o("span",[t._v("￥"+t._s(i.coupon_price))]),o("span",[t._v("原价 "),o("del",[t._v("￥"+t._s(i.origin_price))])])]),t._v(" "),o("p",{staticStyle:{margin:"0"}},[t._v(t._s(i.coupon_info))]),t._v(" "),o("div",{staticClass:"lq"},[o("div",[o("img",{attrs:{src:"static/img/lq.png"}}),t._v(" "),o("span",{directives:[{name:"clipboard",rawName:"v-clipboard:copy",value:i.coupon_click_url,expression:"i.coupon_click_url",arg:"copy"},{name:"clipboard",rawName:"v-clipboard:success",value:t.onCopy,expression:"onCopy",arg:"success"},{name:"clipboard",rawName:"v-clipboard:error",value:t.onError,expression:"onError",arg:"error"}]},[t._v("¥ "+t._s(i.coupon_money))])]),t._v(" "),o("span",[t._v("已售"+t._s(i.sales_num>1e4?i.sales_num/1e4+"万":i.sales_num))])])])])})),t._v(" "),t.isReload?o("div",{staticClass:"error",staticStyle:{"background-color":"red",color:"white",padding:"0.2rem"},on:{click:t.reload}},[t._v("加载失败！点击重新加载")]):t._e()])],1)}))],1)])},staticRenderFns:[]};var n=o("VU/8")(e,a,!1,function(t){o("S7SD"),o("9aeQ")},"data-v-494fb04b",null);i.default=n.exports},S7SD:function(t,i){}});
//# sourceMappingURL=12.dd9ab628d906bb32fec7.js.map