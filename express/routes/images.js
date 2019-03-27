var express = require('express');
var router = express.Router();
const images = require('../app/images');

/* GET users listing. */
router.post('/', function (req, res) {
    var body = req.body;
    if (!body.url) {
        res.json({
            code: 1,
            data: '',
            msg: '链接不能为空'
        });
        return;
    }
    body.url = decodeURIComponent(body.url);
    if (!(/^http(s?)\:\/\//).test(body.url)) {
        res.json({
            code: 1,
            data: '',
            msg: '链接格式不正确，必须以http或https开头'
        });
        return;
    }

    images.run(body.url, function (path) {
        if (!path) {
            res.json({
                code: 1,
                data: '',
                msg: '发生异常，图片无法生成'
            });
        } else {
            res.json({
                code: 0,
                data: 'http://' + req.header('host') + '/images/' + path,
                msg: '返回图片'
            });
        }
    });
});

module.exports = router;
