<?php
define('THIS_PAGE', basename($_SERVER['PHP_SELF']));

$isDebug = isset($argv) ? filter_var($argv[1], FILTER_VALIDATE_BOOLEAN) : false; // when run via PHPStorm
$submittedUrl = isset($_POST['submit']) ? $_POST['url'] : '';
$url = $isDebug ? $argv[2] : $submittedUrl;

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

        <a class="djci" href="https://www.deejay.it/programmi/deejay-chiama-italia/puntate" target="_blank">DJCI
            Reloaded</a>

        <form action="<?= THIS_PAGE ?>" method="post">
            Page:
            <input size="70" type="text" name="url"
                   value="<?= $url ? $url : 'https://www.deejay.it/programmi/deejay-chiama-italia/puntate/page/1' ?>">
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
            preg_match_all(
                '/https:\/\/www.deejay.it\/programmi\/deejay-chiama-italia\/puntate\/deejay-chiama-italia-del-[0-9]{2}-[0-9]{2}-[0-9]{4}/',
                $page,
                $matches
            );
            // $matches ~ https://www.deejay.it/programmi/deejay-chiama-italia/puntate/deejay-chiama-italia-del-26-06-2019
            process_links($matches[0]);

            // process second kind of links, if any
            preg_match_all('/http.*\/audio\/[0-9]{8}\/[0-9]{6}\//', $page, $matches);
            process_links($matches[0]);
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
        CURLOPT_FOLLOWLOCATION => true,   // follow redirects
        CURLOPT_MAXREDIRS => 10,          // stop after 10 redirects
        CURLOPT_ENCODING => '',           // handle compressed
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36', // name of client
        CURLOPT_AUTOREFERER => true,      // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT => 120,           // time-out on response
        // CURLOPT_VERBOSE => true,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    curl_close($ch);

    return $content;
}

function process_links($links)
{
    $processedAlready = [];
    $downloaded = [];

    foreach ($links as $link) {
        $page = get_web_page($link);
        preg_match('/file=http.*mp3/', $page, $match);

        if (isset($processedAlready[$link])) {
            continue;
        }

        $processedAlready[$link] = true;

        if (empty($match)) {
            echo "No files found at $link<br>";
            continue;
        }

        preg_match('/http.*mp3/', $match[0], $targetUrl);

        $srcFile = $targetUrl[0];

        if (isset($downloaded[$srcFile])) {
            continue;
        }

        $pieces = explode('/', $srcFile);
        $filename = end($pieces);
        $filename = substr_replace($filename, '-', 6, 0);
        $filename = substr_replace($filename, '-', 4, 0);
        $filename = 'djci-' . $filename;

        $dir = '/Users/Stefano/Desktop/DJCI';
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }

        $dstfile = $dir . '/' . $filename;
        if (file_exists($dstfile)) {
            echo "<a href=\"$srcFile\">$filename</a> already downloaded.<br>";
            continue;
        }

        echo '<p>';
        echo '<a href="' . $srcFile . '">' . $filename . '</a> ';

        set_time_limit(90);
        if (@copy($srcFile, $dstfile)) {
            $downloaded[$srcFile] = true;
            echo ' downloaded.';
        } else {
            $errors = error_get_last();
            echo ' download ERROR: ' . $errors['type'] . ' - ' . $errors['message'];
        }

        echo '</p>';
    }

}
