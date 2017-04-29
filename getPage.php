<?php

define("THIS_PAGE", basename($_SERVER['PHP_SELF']));

function get_web_page($url) {
    $options = [
        CURLOPT_RETURNTRANSFER => TRUE,   // return web page
        CURLOPT_HEADER => FALSE,          // don't return headers
        CURLOPT_FOLLOWLOCATION => FALSE,  // follow redirects
        CURLOPT_MAXREDIRS => 10,          // stop after 10 redirects
        CURLOPT_ENCODING => "",           // handle compressed
        CURLOPT_USERAGENT => "test",      // name of client
        CURLOPT_AUTOREFERER => TRUE,      // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT => 120,           // time-out on response
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    curl_close($ch);

    return $content;
}

function get_links($list) {
    $length = count($list[0]);
    $links = $list[0];

    for ($i = 0; $i < $length; $i += 2) {
        $page = get_web_page($links[$i]);
        preg_match("/file=http.*mp3/", $page, $match);
        preg_match("/http.*mp3/", $match[0], $targetUrl);
        $filename = str_replace("http://flv.kataweb.it/deejay/audio/deejay_chiama_italia/", "", $targetUrl[0]);
        $filename = substr_replace($filename, "-", 6, 0);
        $filename = substr_replace($filename, "-", 4, 0);
        echo '<p>File '. $i/2 .': <a href="' . $targetUrl[0] . '" download>' . $filename . '</a></p>';
    }
    echo '<br>-- - --<br>';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>getWeb</title>

    <style>
        body { font: 1em "Menlo"; text-align: center; }
        .box, .output { width: 60%; max-width: 500px; min-width: 330px; margin: 2em auto 0; }
        .output { font-size: .8em; }
        form { margin: 2em auto 0; }
        p { line-height: .9em; }
        a.djci { margin: 1em auto; color: black; text-decoration: none; padding: 5px 10px;
          border: 1px solid dimgrey; background: #e1e1e1; border-radius: 3px; }
        a.djci:hover { color: dimgray; }
    </style>
</head>
<div class="box">

    <a class="djci" href="http://www.deejay.it/audio/?reloaded=deejay-chiama-italia" target="_blank">DJCI Reloaded</a>

    <form action="<?= THIS_PAGE ?>" method="post">
        Page: <input size="30" type="text" name="url">
        <input type="submit" name="submit" value="Submit"/>
    </form>
    <br>
</div>

<div class="output">
    <?php
    if (isset($_POST['submit'])) {
        $page = get_web_page($_POST['url']);

        //process first kind of links
        preg_match_all('/http.*\/audio\/[0-9]{8}.[0-9]{1}\/[0-9]{6}\//', $page, $matches);
        get_links($matches);

        //process second kind of links
        preg_match_all('/http.*\/audio\/[0-9]{8}\/[0-9]{6}\//', $page, $matches);
        get_links($matches);
    }
    ?>
</div>
</body>
</html>
