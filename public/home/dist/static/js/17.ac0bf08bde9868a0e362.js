webpackJsonp([17],{ArlG:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i={name:"invation",data:function(){return{QRcode:""}},created:function(){this.gain()},methods:{goBack:function(){console.log("goback"),this.$router.go(-1)},gain:function(){var t=this;this.$POST("/user/invite").then(function(e){console.log(e),200===e.code&&(t.QRcode=e.data[0])}).catch(function(e){t.$toast("连接服务器失败!"),console.log(e)})}}},o={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{attrs:{id:"invation"}},[n("div",{staticClass:"bg"}),t._v(" "),n("van-nav-bar",{staticStyle:{"z-index":"1001"},attrs:{fixed:""}},[n("div",{attrs:{slot:"title"},slot:"title"},[t._v("邀请好友")]),t._v(" "),n("van-icon",{attrs:{slot:"left",name:"arrow-left",color:"black"},on:{click:function(e){t.$router.go(-1)}},slot:"left"})],1),t._v(" "),n("div",{attrs:{id:"container"}},[n("div",{attrs:{id:"bottom"}},[n("img",{staticStyle:{width:"100%",height:"100%"},attrs:{src:t.QRcode,alt:""}})])])],1)},staticRenderFns:[]};var a=n("VU/8")(i,o,!1,function(t){n("Fhi/")},"data-v-9ae5955e",null);e.default=a.exports},"Fhi/":function(t,e){}});
//# sourceMappingURL=17.ac0bf08bde9868a0e362.js.map