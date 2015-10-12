<?php

function buildResult($wf, $input, $count, $arg, $title, $subtitle, $icon, $valid) {
    $result = 0;
    if(preg_match("/.*$input/i","$arg") === 1) {
        $wf->result($count, $arg, $title, $subtitle, $icon, $valid);
        $result = 1;
    }
    return($result);
}

function buildResultEx($wf, $input, $count, $arg, $title, $subtitle, $icon, $valid, $ret) {
    $result = 0;
    $parts = explode("|",$arg);
    if(count($parts) > 0) {
        $arg = $parts[0];
    }
    if(preg_match("/.*$input/i","$arg") === 1) {
        $wf->result($count, $arg, $title, $subtitle, $icon, $valid, $ret);
        $result = 1;
    }
    return($result);
}


function callApi($apiUrl, $postData){
    //获取cookie
    $cookieCh = curl_init($apiUrl);
    curl_setopt($cookieCh, CURLOPT_RETURNTRANSFER, 1);
    // get headers too with this line
    curl_setopt($cookieCh, CURLOPT_HEADER, 1);
    $cookieResult = curl_exec($cookieCh);
    // get cookie
    // multi-cookie variant contributed by @Combuster in comments
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $cookieResult, $cookieMatches);
    $cookies = '';

    if (isset($cookieMatches[1])) {
        $cookies = $cookieMatches[1][0].';';
    }

    $headers = array (
        "Connection: keep-alive",
        "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:41.0) Gecko/20100101 Firefox/41.0",
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Accept-Encoding: gzip,deflate,sdch",
        "Accept-Language: en-US,en;q=0.8",
        "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3" 
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl.'?'.http_build_query($postData));
    curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 80 );
    curl_setopt ( $ch, CURLOPT_COOKIE, $cookies );
    curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
    $rs = curl_exec($ch);
    curl_close($ch);
    
    //使用正则查询出jsonp
    $match = array();
    preg_match('/jsonp\(.*\)/', $rs, $match);
    if (!empty($match)) {
        $jsonpStr = $match[0];
        $jsonpStr = str_replace($postData['cb'].'(', '', $jsonpStr);
        $jsonpStr = str_replace(')', '', $jsonpStr);

        return json_decode($jsonpStr, true);
    }

    return array();
}

?>