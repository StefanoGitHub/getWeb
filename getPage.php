<?php

define("THIS_PAGE", basename($_SERVER['PHP_SELF']));

function get_web_page($url) {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER         => false,  // don't return headers
        CURLOPT_FOLLOWLOCATION => false,   // follow redirects
        CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
        CURLOPT_ENCODING       => "",     // handle compressed
        CURLOPT_USERAGENT      => "test", // name of client
        CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT        => 120,    // time-out on response
    ); 

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $content  = curl_exec($ch);

    curl_close($ch);

    return $content;
}


function get_links($list) {
    $length = count($list[0]);
//    echo $length;
    $links = $list[0];
//        echo'<pre>';
//    var_dump($links);
//        echo'</pre>';
//        die;
    for ($i = 0; $i < $length; $i+=2 ) {
//            if ($i > 0 && $links[$i] != $links[$i-1] ||
//                $i == 0 && $links[$i] != $links[$i+1] ||
//                $i == $length-1) { 
                //echo '<a href="'.$links[$i].'">'.$links[$i].'</a> <br>';
                $page = get_web_page($links[$i]);
                preg_match("/file=http.*mp3/", $page, $match);
//                echo'<pre>';
//            var_dump($match[0]);
//                echo'</pre>';

                preg_match("/http.*mp3/", $match[0], $targetUrl);

                echo '<p>Your file is here: <a href="'. $targetUrl[0] .'" download>' . $targetUrl[0] . '</a></p>';
//            }
    }
    echo '<br>-- - --<br>';
}


echo '
        <!DOCTYPE html>
        <html>

            <body>

                <form action="' . THIS_PAGE . '" method="post"> 

                    Website: <input type="text" name="url" >       
                        <input type="submit" name="submit" value="Submit" />
                </form>
                <br>
    ';


/*if (isset($_POST['submit'])) 
{
    

    $response = get_web_page($_POST['url']);
    preg_match("/file=http.*mp3/", $response, $match);
    preg_match("/http.*mp3/", $match[0], $targetUrl);
    
    
    echo "<p>Fetched:</p> <p>" . $_POST['url'] . "</p>" ;


    echo '<p>Your file is here:</p>
          <h3><a href="'. $targetUrl[0] .'">' . $targetUrl[0] . '</a></h3>';
} */


if (isset($_POST['submit'])) 
{
    $page = get_web_page($_POST['url']);

    //process first kind of links
    preg_match_all('/http.*\/audio\/[0-9]{8}.[0-9]{1}\/[0-9]{6}\//', $page, $matches);
    //    echo'<pre>';
    //    var_dump($matches[0]);
    //    echo'</pre>';
    //    die;
    get_links($matches);

    //process second kind of links
    preg_match_all('/http.*\/audio\/[0-9]{8}\/[0-9]{6}\//', $page, $matches);
    get_links($matches);
    
    
}

echo  '</body>
      </html>';
?>
