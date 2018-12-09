<?php
define('THIS_PAGE', basename($_SERVER['PHP_SELF']));

$url = (isset($_POST['submit'])) ? $_POST['url'] : '';
// $url = $argv[1]; // for debug

?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>getWeb</title>

        <style>
            body {
                font: 1em Menlo;
                text-align: center;
            }
            .box, .output {
                width: 50%;
                height: 100%;
                min-width: 330px;
                margin: 1em auto 0;
            }
            .output { font-size: .8em; float: left; }
            form { margin: 1em auto 0; }
            p { line-height: .9em; }
            a.djci {
                display: inline-block;
                margin: 0 auto 1em;
                color: black;
                text-decoration: none;
                padding: 5px 10px;
                border: 1px solid dimgrey;
                background: #e1e1e1;
                border-radius: 3px;
            }
            a.djci:hover { color: dimgray; }
            code { font-size: .8em; }
        </style>
    </head>
    <body>
    <div class="box">

        <a class="djci" href="http://www.deejay.it/audio/?reloaded=deejay-chiama-italia" target="_blank">DJCI
            Reloaded</a>

            <code>https://media.deejay.it/legacy/audio/deejay_chiama_italia/[yyyymmdd].mp3</code>

        <form action="<?= THIS_PAGE ?>" method="post">
            Page: <input size="30" type="text" name="url" value="<?= $url ?>">
            <input type="submit" name="submit" value="Submit"/>
        </form>
        <br>
    </div>

    <div class="output">
        <?php
        // display url page
        if (!empty($url)) {
            echo '
                <iframe src="' . $url . '" title="DJCI" style="width:100%; height:500px; float:right; border: none;">
                    <p>Your browser does not support iframes.</p>
                </iframe>
            ';
        }
        ?>
    </div>
    <div>
        <?php
        // output links
        if (!empty($url)) {
            // make sure it's https
            $pieces = explode('://', $url);
            if ($pieces[0] === 'http') {
                $url = str_replace('http', 'https', $url);
            }

            $page = get_web_page($url);

            // process first kind of links
            preg_match_all('/http.*\/audio\/[0-9]{8}.[0-9]{1}\/[0-9]{6}\//', $page, $matches);
            process_links($matches);

            // process second kind of links, if any
            preg_match_all('/http.*\/audio\/[0-9]{8}\/[0-9]{6}\//', $page, $matches);
            process_links($matches);
        }

        ?>
    </div>
    </body>
    </html>

<?php

function get_web_page($url)
{
    $options = [
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER => false,          // don't return headers
        CURLOPT_FOLLOWLOCATION => false,  // follow redirects
        CURLOPT_MAXREDIRS => 10,          // stop after 10 redirects
        CURLOPT_ENCODING => '',           // handle compressed
        CURLOPT_USERAGENT => 'test',      // name of client
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

function process_links($list)
{
    $links = $list[0];

    foreach ($links as $i => $link) {
        if ($i % 2 > 0) {
            continue;
        }

        $page = get_web_page($links[$i]);
        preg_match('/file=http.*mp3/', $page, $match);
        preg_match('/http.*mp3/', $match[0], $targetUrl);

        $filename = str_replace('https://media.deejay.it/legacy/audio/deejay_chiama_italia/', '', $targetUrl[0]);
        $filename = substr_replace($filename, '-', 6, 0);
        $filename = substr_replace($filename, '-', 4, 0);
        $filename = 'djci-' . $filename;

        $srcFile = explode('file=', $match[0])[1];
        $dir = '/Users/Stefano/Desktop/DJCI';
        $dstfile = $dir . '/' . $filename;

        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }

        echo '<p>';
        echo 'File ' . ($i / 2 + 1) . ' - <a href="' . $targetUrl[0] . '" download>' . $filename . '</a> ';

        set_time_limit(90);
        if (@copy($srcFile, $dstfile)) {
            echo ' downloaded.';
        } else {
            $errors = error_get_last();
            echo ' download ERROR: ' . $errors['type'] . ' - ' . $errors['message'];
        }

        echo '</p>';
    }

}
