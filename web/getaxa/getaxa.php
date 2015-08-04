<?php
$url="https://www.axa.com.hk/_api/search?schema=download&filterQuery=productIndex:12&sort=productIndex:asc&sort=_score:asc&size=100";
$cookie="forceLocale=zh";
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_HEADER,0);
curl_setopt($ch,CURLOPT_COOKIE,$cookie);
$res=curl_exec($ch);
$output=json_decode($res,true);
curl_close($ch);
file_put_contents('getaxa.json',$res);
print_r($output);
?>


