webpackJsonp([19],{"1OJA":function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r={name:"record",data:function(){return{loading:!0,details:[],status:""}},created:function(){this.detail()},methods:{detail:function(){var t=this,e=this;this.$POST("/user/index").then(function(t){console.log(t),200===t.code&&(e.details=t.data)}).catch(function(e){t.$toast("连接服务器失败!"),console.log(e)})},timestampToTime:function(t){var e=new Date(1e3*t);return e.getFullYear()+"-"+((e.getMonth()+1<10?"0"+(e.getMonth()+1):e.getMonth()+1)+"-")+(e.getDate()+" ")+(e.getHours()+":")+(e.getMinutes()+":")+e.getSeconds()}}},s={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{attrs:{id:"record"}},[a("van-nav-bar",{staticStyle:{"z-index":"1001"},attrs:{fixed:""}},[a("div",{attrs:{slot:"title"},slot:"title"},[t._v("提现记录")]),t._v(" "),a("van-icon",{attrs:{slot:"left",name:"arrow-left",color:"black"},on:{click:function(e){t.$router.go(-1)}},slot:"left"})],1),t._v(" "),a("van-list",{model:{value:t.loading,callback:function(e){t.loading=e},expression:"loading"}},[a("p",{staticStyle:{"margin-top":"2.6rem"}}),t._v(" "),t._l(t.details,function(e,r){return a("div",{key:r,staticClass:"centerBox"},[a("div",{staticClass:"littleBox"},[a("p",[t._v(t._s(t.timestampToTime(e.created_at)))])]),t._v(" "),-1==e.status?a("p",{staticStyle:{"background-color":"#2d7fff","margin-top":"13px","border-radius":"0.3rem",color:"#ffffff",padding:"0 0.3rem"}},[t._v(t._s(["提现失败","已拒绝","待审核","审核通过","提现成功"][parseInt(e.status)+parseInt(2)]))]):2==e.status?a("p",{staticStyle:{"background-color":"green","margin-top":"13px","border-radius":"0.3rem",color:"#ffffff",padding:"0 0.3rem"}},[t._v(t._s(["提现失败","已拒绝","待审核","审核通过","提现成功"][parseInt(e.status)+parseInt(2)]))]):0==e.status?a("p",{staticStyle:{"background-color":"#A2DFFE","margin-top":"13px","border-radius":"0.3rem",color:"#ffffff",padding:"0 0.3rem"}},[t._v(t._s(["提现失败","已拒绝","待审核","审核通过","提现成功"][parseInt(e.status)+parseInt(2)]))]):-2==e.status?a("p",{staticStyle:{"background-color":"red","margin-top":"13px","border-radius":"0.3rem",color:"#ffffff",padding:"0 0.3rem"}},[t._v(t._s(["提现失败","已拒绝","待审核","审核通过","提现成功"][parseInt(e.status)+parseInt(2)]))]):1==e.status?a("p",{staticStyle:{"background-color":"#ff9c67","margin-top":"13px","border-radius":"0.3rem",color:"#ffffff",padding:"0 0.3rem"}},[t._v(t._s(["提现失败","已拒绝","待审核","审核通过","提现成功"][parseInt(e.status)+parseInt(2)]))]):t._e(),t._v(" "),a("p",{staticClass:"money"},[t._v("-"+t._s(e.amount))])])})],2)],1)},staticRenderFns:[]};var o=a("VU/8")(r,s,!1,function(t){a("k/sh")},"data-v-502af425",null);e.default=o.exports},"k/sh":function(t,e){}});
//# sourceMappingURL=19.1f242cb8eaa745c29685.js.map