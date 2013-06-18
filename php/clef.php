<?php
if (!session_id())
    session_start();

$msg="";
$clef_base_url='https://clef.io/api/v1/';
$app_id='562306be5c59cc3f2da25095c05da670';
$app_secret='9fd4e0d1e240e6f95b20a6223c3edbfc';

if (isset($_GET["code"]) && $_GET["code"] != "") {
    $code = $_GET["code"];
    $postdata = http_build_query(
        array(
            'code' => $code,
            'app_id' => $app_id,
            'app_secret' => $app_secret
        )
    );

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    // get oauth code for the handshake
    $context  = stream_context_create($opts);
    $response = file_get_contents($clef_base_url."authorize", false, $context);

    if($response) {
        $response = json_decode($response);

        if(!isset($response['error'])) {
            $access_token = $response['access_token'];

            $opts = array('http' =>
                array(
                    'method'  => 'GET'
                )
            );

            $url = $clef_base_url."info?access_token=".$access_token;

            $context  = stream_context_create($opts);
            $response = file_get_contents($url, false, $context);
            if($response) {
                $response = json_decode($response, true);

                if(!isset($response['error'])) {

                    $info = $response['info'];

                    // reset the user's session
                    if (isset($info['id'])&&($info['id']!='')) {
                        //remove all the variables in the session
                        session_unset();
                        // destroy the session
                        session_destroy();
                        if (!session_id())
                            session_start();

                        $_SESSION['name']     = $result['first_name'].' '.$result['last_name'];
                        $_SESSION['email']    = $result['email'];
                        $_SESSION['user_id']  = $result['id'];
                        $_SESSION['logged_in_at'] = time();  // timestamp in unix time

                        // send them to the member's area!
                        header("Location: http://localhost:8888/membersarea.php");
                    }
                } else {
                    echo "Log in with Clef failed, please try again.";
                }
            }
        } else {
            echo "Log in with Clef failed, please try again.";
        }
        
    } else {
        echo "Log in with Clef failed, please try again.";
    }
}
?>

