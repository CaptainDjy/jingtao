require.config({
    baseUrl: '../static/mobile/js/lib',
    urlArgs: "ver=1.735",
    paths: {
        'jquery': 'jquery.min',
        'css': 'css.min',
        'layer': 'layer_mobile/layer',
        'province':'province_city/province_city',
        'common':'../common',
        'telCountry':'tel-country/intlTelInput',
        'temp':'temp/temp',
        'swiper':'swiper/swiper-3.4.2.jquery.min',
        'qrcode':'qrcode/qrcode.min',
        'kline':'Kline/kline',
        'highstock':'Kline/highstock',
        'alicdn':'alicdn/prism-min',
        'doc_reader':['doc_reader/doc_reader_v2','http://static.bcedocument.com/reader/v2/doc_reader_v2'],
        'baidubce':'baidubce/baidubce-sdk.bundle',
        'gesture':'gesture/jquery.gesture.password',
        'imgScale':'imgScale/tools'

    },
    shim: {
        'layer': {
            deps: ['css!../lib/layer_mobile/need/layer.css']
        },
        'common':{
            deps:['jquery','layer']
        },
        'telCountry':{
            deps:['jquery','css!tel-country/intlTelInput.css']
        },
        'swiper':{
            deps:['jquery','css!../lib/swiper/swiper-3.4.2.min.css']
        },
        'highstock':{
            deps:['jquery']
        },
        'imgScale':{
            deps:['jquery']
        },
        'baidubce':{
            deps:['jquery','https://ss1.bdstatic.com/5eN1bjq8AAUYm2zgoY3K/r/www/cache/ecom/esl/2-1-4/esl.js']
        },
        'alicdn':{
            deps:['css!../lib/alicdn/index.css']
        },
        'gesture':{
            deps:['jquery']
        },
        'kline':{
            deps:['common','highstock']
        },
        "index_tan":{
            deps:["jquery"]
        }

    }
});
