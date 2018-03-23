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
    <body>
    <div class="box">

        <a class="djci" href="https://www.deejay.it/audio/?reloaded=cordialmente" target="_blank">Cordialmente</a>

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
            $page = str_ireplace("\n", ' ', $page);
            $page = str_ireplace("\r", ' ', $page);
            $page = str_ireplace("\t", ' ', $page);

            // process first kind of links
            //            preg_match_all('/http.*\/audio\/[0-9]{8}.[0-9]{1}\/[0-9]{6}\//', $page, $matches);
            preg_match_all('/<span><a href="https:\/\/www\.deejay\.it\/audio\/[0-9]{8}.[0-9]+\/[0-9]{6}\/"/', $page,
                $matches);

            process_links($matches[0]);

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

function process_links($links)
{
    foreach ($links as $link) {
        $link = explode('<span><a href="', $link)[1];
        $link = str_ireplace('"', '', $link);

        $page = get_web_page($link);
        $page = str_ireplace("\n", ' ', $page);
        $page = str_ireplace("\r", ' ', $page);
        $page = str_ireplace("\t", ' ', $page);

        // 'https://cdn.flv.kataweb.it/deejay/audio/cordialmente/20170306.mp3'

        preg_match('/file=http.*mp3/', $page, $match);
        $srcFile = explode('file=', $match[0])[1];

        $filename = end(explode('/', $srcFile));
        $filename = substr_replace($filename, '-', 6, 0);
        $filename = substr_replace($filename, '-', 4, 0);
        $filename = 'cord-' . $filename;

        $dir = '/Users/Stefano/Desktop/DJCI/cordialmente';
        $dstfile = $dir . '/' . $filename;

        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }

        echo '<p>';
        echo 'File ' . $filename . ' - <a href="' . $srcFile . '" download>' . $filename . '</a> ';

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
