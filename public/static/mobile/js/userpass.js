$(function() {
    var dataType = $('body').attr('data-type');
    if (dataType == 'login') {
        //登录页
        $('.am-form-group button.login').click(function () {
            var mobile = $('#user-mobile').val();
            var pass = $('#user-pass').val();
            if (!mobile || !pass) {
                layer.msg('请填写完成再提交', {
                    offset: ['300px'],
                    time: 1000
                });
                return;
            }
            window.authForm.login(mobile,pass)
        })
        $('#user-pass').keydown(function (e) {
            if (e.keyCode == 13) {
                var mobile = $('#user-mobile').val();
                var pass = $('#user-pass').val();
                if (!mobile || !pass) {
                    layer.msg('请填写完成再提交', {
                        offset: ['300px'],
                        time: 1000
                    })
                    return;
                }
                window.authForm.login(mobile,pass)
            }
        })
    }else if(dataType == 'sign-up'){    //注册页
        $('.am-form-group .smscode').click(function(){          //获取验证码
            var mobile = $('#user-mobile').val();
            if(!mobile){
                layer.msg('手机号不能为空',{
                    offset:['300px'],
                    time:1000
                });
                return
            }
            window.authForm.getsmscode(mobile)
        });
        $('.am-form-group button.register').click(function(){
            var mobile = $('#user-mobile').val();
            var referee = $('#user-referee').val();
            var smscode = $('#user-smscode').val();
            var pass = $('#user-pass').val();
            var repass = $('#user-repass').val();
            var name = $('#user-name').val();
            if(!name){
                layer.msg('姓名不能为空',{
                    offset:['300px'],
                    time:1000
                })
                return
            }
            if(!mobile){
                layer.msg('手机号不能为空',{
                    offset:['300px'],
                    time:1000
                })
                return
            }
            if(!referee){
                layer.msg('推荐人不能为空',{
                    offset:['300px'],
                    time:1000
                })
                return
            }
            if(!smscode){
                layer.msg('验证码不能为空',{
                    offset:['300px'],
                    time:1000
                })
                return
            }
            if(!pass||!repass){
                layer.msg('密码不能为空',{
                    offset:['300px'],
                    time:1000
                })
                return
            }
            if(pass != repass){
                layer.msg('两次密码不一致，请重新输入',{
                    offset:['300px'],
                    time:1000
                })
                return
            }
            window.authForm.signup('/api/buyCar/auth/register',{mobile:mobile,referrer:referee,smsCode:smscode,password:pass,name:name,
                rePassword:repass, "<?= Yii::$app->request->csrfParam ?>": "<?= Yii::$app->request->csrfToken ?>"})

        })
    }else if(dataType == 'forget'){     //忘记密码
        $('.am-form-group .smscode').click(function(){          //获取验证码
            var mobile = $('#user-mobile').val();
            if(!mobile){
                layer.msg('手机号不能为空',{
                    offset:['300px'],
                    time:1000
                })
                return
            }
            window.authForm.getsmscode(mobile);
        })
        $('.am-form-group button.forget').click(function(){
            var mobile = $('#user-mobile').val();
            var smscode = $('#user-smscode').val();
            var pass = $('#user-pass').val();
            var repass = $('#user-repass').val();
            if(!mobile){
                layer.msg('手机号不能为空',{
                    offset:['300px'],
                    time:1000
                })
                return
            }
            if(!smscode){
                layer.msg('验证码不能为空',{
                    offset:['300px'],
                    time:1000
                })
                return
            }
            if(!pass||!repass){
                layer.msg('密码不能为空',{
                    offset:['300px'],
                    time:1000
                })
                return
            }
            window.authForm.forget({mobile:mobile,smsCode:smscode,password:pass,rePassword:repass});

        })
    }
})





