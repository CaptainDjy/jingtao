$(function() {
    // 读取body data-type 判断是哪个页面然后执行相应页面方法，方法在下面。
    var dataType = $('body').attr('data-type');
    for (key in pageData) {
        if (key == dataType) {
            pageData[key]();
        }
    }
    autoLeftNav();
    $(window).resize(function() {
        // autoLeftNav();
    });
    $('.tpl-skiner-toggle').on('click', function() {
        $('.tpl-skiner').toggleClass('active');
    })
    $('body').attr('class', 'theme-white');
})
var mainUrl = 'http://buycar.www.com/api/buyCar/';
// 页面数据
var pageData = {
    // ===============================================
    // 首页
    // ===============================================
    'index': function indexData() {
    }
};
var dataType = $('body').attr('data-type');
//页面跳转后active
var boy = $('body').find('li .'+dataType);
boy.addClass('active');

//报单中心显示判定
if(dataType != 'login'&&dataType != 'forget'&&dataType != 'sign-up' ){
    for(var i = 0;i < boy[0].classList.length;i ++){
        if(boy[0].classList[i] == 'boy'){
            boy.parent().parent().show();
        }
    }
    var nickname = $.AMUI.store.get('userInfo').name;
    if($.AMUI.store.get('userInfo').report == 1){
        $('a.center').parent().show();
        $('a.shen').parent().hide();
    }else{
        $('a.center').parent().hide();
        $('a.shen').parent().show();
    }
    if($.AMUI.store.get('userInfo').partner == 1){
        $('a.agency').parent().hide();
    }else{
        $('a.agency').parent().show();
    }
    if(!nickname){
        layer.msg('请先到个人资料修改真实姓名，才能报单', {
            offset: ['300px'],
            time: 1000
        });
    }
}
if($.AMUI.store.get('userInfo')){
    var str = $.AMUI.store.get('userInfo').name ? $.AMUI.store.get('userInfo').name : '未设置';
    $('#left_name').html(str)
    $('#left_id').html($.AMUI.store.get('userInfo').id)
}

$('span.nickname').html(nickname);
//页面跳转
$('.sidebar-nav-link a').click(function(){
    var gourl = $(this).attr('go-type');
    if(gourl){
        window.location.href = "/site/"+gourl;
    }
});

// 侧边菜单开关
function autoLeftNav() {
    $('.tpl-header-switch-button').on('click', function() {
        if ($('.left-sidebar').is('.active')) {
            if ($(window).width() > 1024) {
                $('.tpl-content-wrapper').removeClass('active');
            }
            $('.left-sidebar').removeClass('active');
        } else {

            $('.left-sidebar').addClass('active');
            if ($(window).width() > 1024) {
                $('.tpl-content-wrapper').addClass('active');
            }
        }
    })

    if ($(window).width() < 1024) {
        $('.left-sidebar').addClass('active');
    } else {
        $('.left-sidebar').removeClass('active');
    }
}
// 侧边菜单
$('.sidebar-nav-sub-title').on('click', function() {
    $(this).siblings('.sidebar-nav-sub').slideToggle(80)
        .end()
        .find('.sidebar-nav-sub-ico').toggleClass('sidebar-nav-sub-ico-rotate');
})
//验证码倒计时
function getSms(ele,time){
    var num = time;
    var _this = ele;
    $(_this).html(num).addClass('am-disabled');
    var myTime = setInterval(function(){
        if(num > 1){
            num --;
            $(_this).html(num);
        }else{
            $(_this).html('重新发送').removeClass('am-disabled');
            clearInterval(myTime);
        }
    },1000)
}
