<?php
$urls = array(
    'http://www.hkgbf.com/',
    'http://www.hkgbf.com/index.php?c=list&cs=pros',
    'http://www.hkgbf.com/index.php?c=list&cs=join_us',
    'http://www.hkgbf.com/index.php?c=msg&ts=contact',
    'http://www.hkgbf.com/index.php?c=list&cs=register&',
    'http://www.hkgbf.com/index.php?c=list&cs=huanqiugupiao&',
    'http://www.hkgbf.com/index.php?c=list&cs=huanqiujijin&',
    'http://www.hkgbf.com/index.php?c=list&cs=xianggangbaoxian&',
    'http://www.hkgbf.com/index.php?c=list&cs=ipo&',
    'http://www.hkgbf.com/index.php?c=list&cs=fund_products&',
    'http://www.hkgbf.com/index.php?c=list&cs=shijiejijijingongsijianjie&',
    'http://www.hkgbf.com/index.php?c=list&cs=insurance_products&',
    'http://www.hkgbf.com/index.php?c=list&cs=insurance_notice&',
    'http://www.hkgbf.com/index.php?c=list&cs=insurance_case&',
    
);
$api = 'http://data.zz.baidu.com/urls?site=www.hkgbf.com&token=HK9reLk0R8EcLQDC';
$ch = curl_init();
$options =  array(
    CURLOPT_URL => $api,
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => implode("\n", $urls),
    CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
);
curl_setopt_array($ch, $options);
$result = curl_exec($ch);
echo $result;
?>