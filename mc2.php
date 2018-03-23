<?php

define("THIS_PAGE", basename($_SERVER['PHP_SELF']));

function get_web_page($url)
{
    $options = [
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER => false,          // don't return headers
        CURLOPT_FOLLOWLOCATION => false,  // follow redirects
        CURLOPT_MAXREDIRS => 10,          // stop after 10 redirects
        CURLOPT_ENCODING => "",           // handle compressed
        CURLOPT_USERAGENT => "test",      // name of client
        CURLOPT_AUTOREFERER => true,      // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT => 120,           // time-out on response
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    curl_close($ch);

    return $content;
}

function get_links($list)
{
    $links = $list[0];

    foreach ($links as $i => $link) {
        if ($i % 2 > 0) {
            continue;
        }

        $page = get_web_page($link);
        preg_match("/file=http.*mp3/", $page, $match);

        $srcFile = explode('file=', $match[0])[1];
        $filename = 'mc2-' . end(explode("/", $srcFile));

        $dir = '/Users/Stefano/Desktop/DJCI/MC2';
        $dstfile = $dir . '/' . $filename;

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!@copy($srcFile, $dstfile)) {
            $errors = error_get_last();
            echo "COPY ERROR: " . $errors['type'];
            echo '<br>';
            echo $errors['message'];
        } else {
            echo "$filename copied from remote!";
            echo '<br>';
        }
    }
}

$url = 'https://www.deejay.it/audio/?reloaded=mc2';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>getWeb</title>

    <style>
        body { font: 1em "Menlo"; text-align: center; }
        .box, .output { width: 60%; max-width: 500px; min-width: 330px; margin: 1em auto 0; }
        .output { font-size: .8em; }
        form { margin: 1em auto 0; }
        p { line-height: .9em; }
        a.djci {
            margin: 1em auto;
            color: black;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid dimgrey;
            background: #e1e1e1;
            border-radius: 3px;
        }
        a.djci:hover { color: dimgray; }
    </style>
</head>

<div class="output" style="width:50%; height:100%; float:left;">
    <?php
    // output links
    $page = get_web_page($url);

    // get links to mp3 page
    preg_match_all('/https:\/\/www.deejay.it\/audio\/[0-9]{8}.[0-9]+\/[0-9]{6}\//', $page, $matches);
    // get files
    process_links($matches);
    ?>
</div>
</body>
</html>
