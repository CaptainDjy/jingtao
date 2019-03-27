#!/bin/bash
echo '正在结束进程……'
kill -9 $(ps aux|grep 'swoole/index'|grep -v grep|awk '{print $2}')
echo '正在重启进程……'
php /data/wwwroot/chain/public_html/yii swoole/index
echo '重启完成'
