require.config({
    baseUrl: 'static/libs',
    paths: {
        'iCheck': 'iCheck/icheck.min',
        'css': 'css.min',
        'layer': 'layer/layer',
        'orgchart': 'orgchart/jquery.orgchart',
    },
    shim: {
        'iCheck':{
            deps: ['css!../libs/iCheck/skins/square/blue.css']
        },
        'layer':{
            exports: "layer",
            deps: ['css!../libs/layer/skin/default/layer.css']
        },
        'orgchart':{
            deps: ['css!../libs/orgchart/jquery.orgchart.css']
        },
    }
});
