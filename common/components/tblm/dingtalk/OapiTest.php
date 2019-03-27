<?php
include "TopSdk.php";
date_default_timezone_set('Asia/Shanghai');
$c = new DingTalkClient(DingTalkConstant::$CALL_TYPE_OAPI, DingTalkConstant::$METHOD_POST , DingTalkConstant::$FORMAT_JSON);

$req = new HttpdnsGetRequest;
$req->putOtherTextParam("auth_corpid", "dingc365fcabbf733c3535c2f4657eb6378f");

var_dump($c->executeWithSuiteTicket($req,"https://pre-oapi.dingtalk.com/service/get_auth_info", "suitezmpdnvsw4syq53g6", "QhQQgAbHTmQ7wXhpfH4EF2aefd_ONzg_GhE7eBrV6PkuGbtBX501RU5dAaK8LFSZ", "testSuiteTicket"));

//    DingTalkClient client = new DefaultDingTalkClient("https://oapi.dingtalk.com/gettoken");
//    OapiGettokenRequest request = new OapiGettokenRequest();
//    request.setCorpid("dingc95d22c053c528ae");
//    request.setCorpsecret("y2bvq4CbSV0TupI0bTg-Y3BSzyl3fhyJOLaUAkbIgj34L1lcrspjKL9A8FdLgNs4");
//    request.setHttpMethod("GET");
//
//    OapiGettokenResponse response = client.execute(request, null);
//    System.out.println(response.getBody());
?>