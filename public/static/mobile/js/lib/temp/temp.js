/**
 * Created by liu on 15-9-19.
 */

var jo={};

(function(_) {

   var _uid=0;
    _.uid= function () {
        return ++_uid
    }

    _.escape = function(string) {
        return (''+string).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#x27;').replace(/\//g,'&#x2F;');
    };
    var txnd = {};
    _._textNode = function(pid, s) {
        if (!(pid in txnd))
            txnd[pid] = [];
        txnd[pid].push(s);
        s= '<abbr id="jo_txnd'+txnd[pid].length+'">joO txnd</abbr>';
        return s;
    };
    _.template_end = function(pid) {
        var ps = txnd[pid], abbr;
        if (!ps || !ps.length) return;
        for (var i=1,l=ps.length; i<=l; i++) {
            abbr = _.G('jo_txnd'+i);
            abbr.parentNode.replaceChild(
                document.createTextNode(ps[i-1]), abbr );
        }
        abbr = ps = null;
        delete txnd[pid];
    };

    _.templateSettings = {
        evaluate    : /{%([\s\S]+?)%}/g,
        interpolate : /{%=([\s\S]+?)%}/g,
        escape      : /{%-([\s\S]+?)%}/g
    };

    _.template = function(str) {
        var pid = this.uid();
        var c  = _.templateSettings;
        var tmpl = 'var __p=[],print=function(){__p.push.apply(__p,arguments);};' +
            'with(obj||{}){__p.push(\'' +
            str.replace(/\\/g, '\\\\')
                .replace(/'/g, "\\'")
                .replace(c.escape, function(match, code) {
                    return "',jo._textNode("+pid+"," + code.replace(/\\'/g, "'") + "),'";
                })
                .replace(c.interpolate, function(match, code) {
                    return "'," + code.replace(/\\'/g, "'") + ",'";
                })
                .replace(c.evaluate || null, function(match, code) {
                    return "');" + code.replace(/\\'/g, "'")
                        .replace(/[\r\n\t]/g, ' ') + "__p.push('";
                })
                .replace(/\r/g, '\\r')
                .replace(/\n/g, '\\n')
                .replace(/\t/g, '\\t')
            + "');}return __p.join('');";
        var func = new Function('obj', tmpl);
        return [func, pid];
    };
    _.txnd = txnd;  // 反正也不打算长久用，先暴露出来吧，方便调试
})(jo);
jo.taTpl = function(taSel, data, posSel) {
    if (!data) return;
    var html = taSel.html();
    html = jo.template(html)[0](data); // null 值会被 join 干掉

    if (posSel) {
        taSel.remove();
        taSel = posSel;
    }
    taSel.replaceWith(html);
};

jo.reTpl = function(taSel, data, posSel) {
    if (!data) return;
    var html = taSel.html();
    html = jo.template(html)[0](data);
    posSel.empty().append(html);
};