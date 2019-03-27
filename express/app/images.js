const webshot = require('webshot');
const fs = require('fs');
const crypto = require('crypto');
var options = {
    screenSize: {
        width: 720,
        height: 1280
    },
    shotSize: {
        width: 720,
        height: 'all'
    },
    timeout: 10000,
    defaultWhiteBackground: true,
    quality: 80,
    userAgent: 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.20 (KHTML, like Gecko) Mobile/7B298g'
};
module.exports = {
    run: function (url, callback) {
        var fileName = crypto.createHash('md5').update(url).digest('hex') + '.png';
        var path = 'public/images/' + fileName;

        var exits = fs.existsSync(path);
        if (exits) {
            var stat = fs.statSync(path);
            if (stat.size > 10000) {
                if (stat.ctimeMs + 3600 * 1000 * 2 > new Date().getTime()) {
                    if (callback) callback(fileName);
                    return;
                }
            }
        }

        var readerStream = webshot(url, options);
        var writerStream = fs.createWriteStream(path, {encoding: 'binary'});
        readerStream.on('data', function (data) {
            writerStream.write(data.toString('binary'), 'binary')
        });
        readerStream.on('error', function (err) {
            console.log('err', err);
        });
        readerStream.on('end', function () {
            var stat = fs.statSync(path);
            if (stat.size < 10000) {
                if (callback) callback('');
            } else {
                if (callback) callback(fileName);
            }
        });
    }
};
