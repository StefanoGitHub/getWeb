<?php

define("THIS_PAGE", basename($_SERVER['PHP_SELF']));

function get_web_page($url) {
    $options = [
        CURLOPT_RETURNTRANSFER => TRUE,   // return web page
        CURLOPT_HEADER => FALSE,  // don't return headers
        CURLOPT_FOLLOWLOCATION => FALSE,   // follow redirects
        CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
        CURLOPT_ENCODING => "",     // handle compressed
        CURLOPT_USERAGENT => "test", // name of client
        CURLOPT_AUTOREFERER => TRUE,   // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT => 120,    // time-out on response
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
        echo '<p>Your file is here: <a href="' . $targetUrl[0] . '" download>' . $targetUrl[0] . '</a></p>';
    }
    echo '<br>-- - --<br>';
}


echo '
        <!DOCTYPE html>
        <html>

            <body>
                <a href="http://www.deejay.it/audio/?reloaded=deejay-chiama-italia" target="_blank">Reloaded DJCI</a>

                <br>
                <br>

                <form action="' . THIS_PAGE . '" method="post"> 

                    Website: <input type="text" name="url" >       
                        <input type="submit" name="submit" value="Submit" />
                </form>
                <br>
    ';

if (isset($_POST['submit'])) {
    $page = get_web_page($_POST['url']);

    //process first kind of links
    preg_match_all('/http.*\/audio\/[0-9]{8}.[0-9]{1}\/[0-9]{6}\//', $page, $matches);
    get_links($matches);

    //process second kind of links
    preg_match_all('/http.*\/audio\/[0-9]{8}\/[0-9]{6}\//', $page, $matches);
    get_links($matches);


}

echo '</body>
      </html>';
