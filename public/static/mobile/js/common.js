require(['jquery'],function () {
    //msg common
    window.commonFun=(function(){
        var telLimit=pwdShow=true;
        var timer;
        return {
            msg:function(con){//信息提示弹框
                layer.open({
                    type:0,
                    style:'top:-100px',
                    content: con,
                    skin: 'msg',
                    time: 2, //2秒后自动关闭
                });
            },
            setFun:function (key, data) {
                return window.localStorage.setItem(key, window.JSON.stringify(data));
            },
            getFun:function (key) {
                return window.JSON.parse(window.localStorage.getItem(key));
            },
            remove:function (key) {
                return window.localStorage.removeItem(key);
            },
            userStorageSave:function (obj,rember) {//{tel:'',pwd:''}本地保存用户列表
                var userCH=this.getFun('userCH') || {'userList':[]};//{tel:'',pwd:''}
                var haveNum=(function(){
                    for(var i=0;i<userCH.userList.length;i++){
                        if(obj.tel==userCH.userList[i].tel){
                            return true;
                        }
                    }
                })();
                if(haveNum!==true){//不存在
                    if(rember){//记住密码
                        var tempObj=obj;
                        userCH.userList.unshift(tempObj);
                        this.setFun('userCH',userCH);
                    }else{//不记住密码
                        var tempObj={'tel':obj.tel,'pwd':''};
                        userCH.userList.unshift(tempObj);
                        this.setFun('userCH',userCH);
                    }
                }else{//存在
                    if(rember){//记住密码
                        for(var i=0;i<userCH.userList.length;i++){
                            if(obj.tel==userCH.userList[i].tel){
                                userCH.userList[i].pwd=obj.pwd;
                                this.setFun('userCH',userCH);
                                return;
                            }
                        }
                    }else{
                        //已存在
                        for(var i=0;i<userCH.userList.length;i++){
                            if(obj.tel==userCH.userList[i].tel){
                                //删除再添加
                                userCH.userList.splice(i,1);
                                userCH.userList.unshift(obj);
                                this.setFun('userCH',userCH);
                                return;
                            }
                        }

                    }
                }
            },
            userStorageRemove:function (str) {//删除账号列表
                var _that=this;
                var userCH=this.getFun('userCH');
                for(var i=0;i<userCH.userList.length;i++){
                    if(userCH.userList[i].tel==str){//删除数组元素
                        userCH.userList.splice(i,1);
                        _that.setFun('userCH',userCH);
                        return;
                    }
                }
            },
            imgCodeFun:function (obj) {//刷新图片二维码
                var url=$(obj).attr('src');
                var start=url.indexOf('?');
                if(start>0){
                    url=url.substr(0,start)+"?"+Math.random();
                    $(obj).attr('src',url);
                }else{
                    url=url+"?"+Math.random();
                    $(obj).attr('src',url);
                }
            },
            mobilCheck:function (tel) {//手机号校验
                var mobilCheck=/^1\d{10}$/;
                if(!mobilCheck.test(tel)){
                    this.msg('请输入正确的手机号码');
                    return true;
                }
            },
            mobilSub:function (tel,obj) {//手机号截取
                var tempTel=tel.substr(0,3)+'****'+tel.substr(7);
                $('.'+obj).text(tempTel);
            },
            userIDcheck : function (ID) {
                //身份证号码校验
                //var userIDcheck=/(^\d{15}$)|(^\d{17}(\d|[X,x])$)/;
                var userIDcheck=/(^\d{10}(([0][1-9])|([1][0-2]))(([0][1-9])|([1-2][0-9])|([3][0-1]))[\d]{2}$)|(^\d{10}(([0][1-9])|([1][0-2]))(([0][1-9])|([1-2][0-9])|([3][0-1]))[\d]{3}(\d|[X,x])$)/;
                if(!userIDcheck.test(ID)){
                    this.msg(' 请输入正确的身份证号码');
                    return true;
                }
            },
            userNamecheck : function (ID) {//姓名校验
                var userNamecheck=/^[\u4e00-\u9fa5]{2,15}$/;
                if(!userNamecheck.test(ID)){
                    this.msg('请输入正确的姓名');
                    return true;
                }
            },
            cardNumcheck : function (ID) {//银行卡号效验
                var userNumcheck=/^\s*\d{16,19}$/;
                if(!userNumcheck.test(ID)){
                    this.msg('请输入正确的银行卡号');
                    return true;
                }
            },
            getTelCode:function (obj,tel,type,arr) {
                //获取短信验证码
                var Obj={
                    mobile:tel,
                    'type':type
                };
                var str="Obj['"+arr[0]+"']="+arr[1];
                eval(str);
                console.log(Obj);return;
                if(telLimit){
                    telLimit=false;
                    clearInterval(timer);
                    var time=time || 120;
                    $.post('/auth/sms-verifycode', {
                        'mobile': mobile,
                        "<?= Yii::$app->request->csrfParam ?>": "<?= Yii::$app->request->csrfToken ?>"
                    }, function (response) {
                        if (response.code === '0') {
                            clearInterval(timer);
                            var time=120;
                            timer=setInterval(function(){
                                time--;
                                $('.'+obj).text(time+' S');
                                if(time<=0){
                                    clearInterval(timer);
                                    $('.'+obj).text('重新获取');
                                    telLimit=true;
                                }
                            },1000);
                        }
                    });


                    timer=setInterval(function(){
                        time--;
                        $('.'+obj).text(time+' S');
                        if(time<=0){
                            clearInterval(timer);
                            $('.'+obj).text('重新获取');
                            telLimit=true;
                        }
                    },1000);
                }else{
                    this.msg('请不要重复获取验证码');
                }

            },
            pwdShowFun:function (obj) {//密码点击显示隐藏
                if(pwdShow){//显示密码
                    pwdShow=false;
                    $(obj).css('color','#999').prev('input').attr('type','text');
                }else{//隐藏密码
                    pwdShow=true;
                    $(obj).css('color','#008fe0').prev('input').attr('type','password');
                }
            },
            strTotime:function (val){
                //时间戳转换的问题
                var obj=$(val).text();
                var newDate = new Date();
                newDate.setTime(obj*1000);

                var year=newDate.getFullYear();
                var month=newDate.getMonth()+1;
                month= month<10 ? "0"+month:month;
                var date=newDate.getDate();
                date=date<10?"0"+date:date;

                var hour=newDate.getHours();
                hour=hour<10?"0"+hour:hour;
                var minute=newDate.getMinutes();
                minute=minute<10?"0"+minute:minute;
                var seconds=newDate.getSeconds();
                seconds=seconds<10?"0"+seconds:seconds;

                $(val).text(year+"-"+month+"-"+date+" "+hour+":"+minute+":"+seconds);
            }



        }

    })();

    //codeDraw
    window.codeDraw=function(){
        return{
            drawFun:function (id,url,width,height,colorDark,colorLight) {
                var widthN= width ||300;
                var heightN= height ||300;
                var colorDarkN= colorDark || '#000000';
                var colorLightN= colorLight ||'#ffffff';
                var qrcode = new QRCode(document.getElementById(id), {
                    text: url,
                    width:widthN,
                    height: heightN,
                    colorDark : colorDarkN,
                    colorLight : colorLightN,
                    correctLevel : QRCode.CorrectLevel.H
                });
            }
        }
    }();

    window.shareFun=function () {
        return{
            shareSina:function() {
                //分享到新浪微博
                var sharesinastring = 'http://service.weibo.com/share/share.php?title=' + $("#title").val() + '&url=' + $("#url").val();
                window.location.href = sharesinastring;
            },
            shareQQzone :function(obj){
                /*var p = {
                    url:location.href,
                    showcount:'0',/!*是否显示分享总数,显示：'1'，不显示：'0' *!/
                    desc:'',/!*默认分享理由(可选)*!/
                    summary:'',/!*分享摘要(可选)*!/
                    title:'',/!*分享标题(可选)*!/
                    site:'满艺网',/!*分享来源 如：腾讯网(可选)*!/
                    pics:'', /!*分享图片的路径(可选)*!/
                    style:'203',
                    width:98,
                    height:22
                };*/
                //分享到QQ空间
                var sharesinastring = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?title=' + p.site + '&url=' + p.url + '&site="满艺网"';
                // var sharesinastring = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?title=' + $("#title").val() + '&url=' + $("#url").val() + '&site="满艺网"';
                window.location.href = sharesinastring;
            },
            shareQQ: function() {
                var p = {
                    url: location.href, /*获取URL，可加上来自分享到QQ标识，方便统计*/
                    desc: '', /*分享理由(风格应模拟用户对话),支持多分享语随机展现（使用|分隔）*/
                    title: '', /*分享标题(可选)*/
                    summary: '', /*分享摘要(可选)*/
                    pics: '', /*分享图片(可选)*/
                    flash: '', /*视频地址(可选)*/
                    site: '满艺网', /*分享来源(可选) 如：QQ分享*/
                    style: '201',
                    width: 32,
                    height: 32
                };
                //分享到QQ
                var sharesinastring = 'http://connect.qq.com/widget/shareqq/index.html?title=' + $("#title").val() + '&summary=' + $("#url").val() + '&url=' + $("#url").val() + '&site="满艺网"';
                window.location.href = sharesinastring;
            },
            shareQQweibo : function (url,title,from,pic) {
                console.log(arguments)
                /*var p = {
                    url: url, /!*获取URL，可加上来自分享到QQ标识，方便统计*!/
                    title: title, /!*分享标题(可选)*!/
                    pic: pic, /!*分享图片(可选)*!/
                    site: from /!*分享来源(可选) 如：QQ分享*!/
                };*/
                //分享到腾讯微博
                var sharesinastring = 'http://v.t.qq.com/share/share.php?title=' +title + '&url=' + url + '&site='+from;
                window.location.href = sharesinastring;
            }

        }
    }();


})


//window redirect
function browserRedirect() {//浏览器重定向
    var sUserAgent = navigator.userAgent.toLowerCase();
    var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
    var bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";
    var bIsMidp = sUserAgent.match(/midp/i) == "midp";
    var bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";
    var bIsUc = sUserAgent.match(/ucweb/i) == "ucweb";
    var bIsAndroid = sUserAgent.match(/android/i) == "android";
    var bIsCE = sUserAgent.match(/windows ce/i) == "windows ce";
    var bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile";
    if (!(bIsIpad || bIsIphoneOs || bIsMidp || bIsUc7 || bIsUc || bIsAndroid || bIsCE || bIsWM) ){
        //浏览器
        $('.QQKF li').each(function () {
            var QQ=$(this).children('a').attr('data-qq');
            var url='tencent://message/?uin='+QQ+'&Site=http://vps.shuidazhe.com&Menu=yes';
            $(this).children('a').attr('href',url);
        });

    }else{
        //手机
        $('.QQKF li').each(function () {
            var QQ=$(this).children('a').attr('data-qq');
            var url='mqqwpa://im/chat?chat_type=wpa&uin='+QQ+'&version=1&src_type=web&web_src=oicqzone.com';
            $(this).children('a').attr('href',url);
        });
    }
}
//share obj
//img
function imgSizeSet(name,callBack){
    var heightTemp,sizeScale;
    var imgTemp=$('.'+name).find('img');
    for(var i=0;i<imgTemp.length;i++){
        if($(imgTemp[i]).css("display")!='none'){
            setTimeout(function(){
                var tempW=parseInt($(imgTemp[i]).css("width")).toFixed(0);
                var tempH=parseInt($(imgTemp[i]).css("height")).toFixed(0);
                setHeight(tempW,tempH,$(imgTemp[i]));
            },500);
            callBack && callBack();//回调函数
            return;
        }
    }
    function setHeight(tempW,tempH,obj){
        obj.parents('.'+name).find('.index-hotSell').css({"height":tempH+"px","width":tempW+"px","lineHeight":tempH+"px"});
        obj.parents('.'+name).find('.index-hotSell').children("span").css({"lineHeight":tempH+"px"});
        obj.parents('.'+name).find('img').css({"height":tempH+"px","width":tempW+"px"});
    }
};
//var windowW windowH  横竖屏变换的时候重新加载


