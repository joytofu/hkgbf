<?php
header("Content-type:text/html;charset:utf-8");
$url="https://www.axa.com.hk/_api/search?schema=download&filterQuery=categoryNumber:3%20AND%20_exists_:categoryNumber&sort=productIndex:asc&sort=_score:asc&size=100";
$res=file_get_contents($url);
echo $res;
//$output=json_decode($res,true);
//print_r($output);