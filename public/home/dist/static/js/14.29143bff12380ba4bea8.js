webpackJsonp([14],{Cq4Y:function(i,t){},ZqFE:function(i,t){},oeCJ:function(i,t,o){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var e={name:"list",data:function(){return{active:0,value:"",list:[],nav:[],loading:!1,finished:!1,pageNum:1,pageSize:10,index:0,isReload:!1,myload:!1,keyWord:"",toUrl:"",toToken:""}},created:function(){this.toUrl=this.$route.query.id,this.toToken=this.$route.query.token},methods:{onCopy:function(i){this.$toast("复制成功")},onError:function(){this.$toast("复制失败")},onSearch:function(){alert(this.value)},onLoad:function(){this.loading=!0,this.getListById(),this.pageNum++},getListById:function(){var i=this,t={haveNoToken:!0,page:this.pageNum,alimm_pid:this.toToken,cid:1,sort:0};this.$POST(this.toUrl,t,!1).then(function(t){console.log(t),200===t.code?t.data.length>0?(i.list=t.data,t.data.length<20&&(i.finished=!0,i.$toast("已经到底了！")),i.loading=!1):(i.finished=!0,i.$toast("没有数据了亲!")):i.finished=!0,i.loading=!1,console.log("加载数据成功!")}).catch(function(t){i.loading=!1,i.finished=!0,i.pageNum--,i.isReload=!0,console.log("获取数据失败!")})},reload:function(){this.finished=!1,this.isReload=!1},load:function(){this.findComType(),this.myload=!1}}},s={render:function(){var i=this,t=i.$createElement,o=i._self._c||t;return o("div",{staticClass:"flex-page",attrs:{id:"chanelList2"}},[o("div",{staticClass:"page-header"},[o("van-nav-bar",[o("div",{attrs:{slot:"title"},slot:"title"},[i._v("\n        商品列表\n      ")]),i._v(" "),o("van-icon",{attrs:{slot:"left",name:"arrow-left",color:"black"},on:{click:function(t){i.$router.go(-1)}},slot:"left"})],1)],1),i._v(" "),o("div",{staticClass:"page-body"},[o("div",{staticClass:"bg"}),i._v(" "),i.myload?o("van-button",{staticClass:"mybtn",attrs:{size:"large",square:"",type:"primary"},on:{click:i.load}},[i._v("重新加载")]):i._e(),i._v(" "),o("van-list",{attrs:{offset:100},on:{load:i.onLoad},model:{value:i.loading,callback:function(t){i.loading=t},expression:"loading"}},[i._l(i.list,function(t,e){return o("div",{key:e,staticClass:"good-list",on:{click:function(o){i.$router.push({path:"/goodsDetail",query:{coupon_links:"https:"+t.coupon_link,commission_moneys:t.commission_money,images:t.thumb,yishou:t.sales_num,id:t.origin_id,delprice:t.origin_price,title:t.title,nowPrice:t.coupon_price,quanJine:t.coupon_money,thumbs:t.thumb}})}}},[o("img",{directives:[{name:"lazy",rawName:"v-lazy",value:t.thumb,expression:"i.thumb"}],staticStyle:{width:"140px",height:"160px"},attrs:{src:t.thumb}}),i._v(" "),o("div",[o("span",{staticClass:"searchlist-goodsname",staticStyle:{overflow:"hidden","text-overflow":"ellipsis",display:"-webkit-box","-webkit-line-clamp":"2","-webkit-box-orient":"vertical"}},[i._v(i._s(t.title))]),i._v(" "),o("div",[o("span",[i._v("￥"+i._s(t.coupon_price))]),o("span",[i._v("原价"),o("del",[i._v(i._s(t.origin_price))])])]),i._v(" "),o("p",{staticStyle:{margin:"0"}},[i._v(i._s(t.coupon_info))]),i._v(" "),o("div",{staticClass:"lq"},[o("div",[o("img",{attrs:{src:"static/img/lq.png"}}),i._v(" "),o("span",{directives:[{name:"clipboard",rawName:"v-clipboard:copy",value:t.coupon_click_url,expression:"i.coupon_click_url",arg:"copy"},{name:"clipboard",rawName:"v-clipboard:success",value:i.onCopy,expression:"onCopy",arg:"success"},{name:"clipboard",rawName:"v-clipboard:error",value:i.onError,expression:"onError",arg:"error"}]},[i._v("¥ "+i._s(t.coupon_money))])]),i._v(" "),o("span",[i._v("已售"+i._s(t.sales_num>1e4?t.sales_num/1e4+"万":t.sales_num))])]),i._v(" "),o("div",{staticClass:"yg"},[o("div",[i._v("预估赚￥"+i._s(t.commission_money))]),i._v(" "),o("div",[i._v("升级赚￥5.20")])])])])}),i._v(" "),i.isReload?o("div",{staticClass:"error",staticStyle:{"background-color":"red",color:"white",padding:"0.2rem"},on:{click:i.reload}},[i._v("加载失败！点击重新加载")]):i._e()],2)],1)])},staticRenderFns:[]};var a=o("VU/8")(e,s,!1,function(i){o("ZqFE"),o("Cq4Y")},"data-v-0a1dfe50",null);t.default=a.exports}});
//# sourceMappingURL=14.29143bff12380ba4bea8.js.map