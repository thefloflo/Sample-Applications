<?php
if (!session_id()) {session_start();};

$msg="";
$success=0;
$clef_base_url='https://clef.io/api/v1/';
$app_id='562306be5c59cc3f2da25095c05da670';
$app_secret='9fd4e0d1e240e6f95b20a6223c3edbfc';

if (isset($_GET["code"]) && $_GET["code"] != ""):
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

    $context  = stream_context_create($opts);
    $response = file_get_contents($clef_base_url."authorize", false, $context);

    if($response != false):
        $response = json_decode($response);
        $access_token = $response->{'access_token'};

        $opts = array('http' =>
            array(
                'method'  => 'GET'
            )
        );

        $url = $clef_base_url."info?access_token=".$access_token;

        $context  = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);
        if($response && $response != false):
            $response = json_decode($response, true);
            $info = $response['info'];

            if (isset($info['id'])&&($info['id']!='')):
                //remove all the variables in the session
                session_unset();
                // destroy the session
                session_destroy();
                if (!session_id()) {session_start();};

                $_SESSION['name']     = $result['first_name'].' '.$result['last_name'];
                $_SESSION['email']    = $result['email'];
                $_SESSION['user_id']  = $result['id'];

                $success=1;
            endif;
        else:
            echo "Log in with Clef failed, please try again.";
        endif;
    endif;
endif;
?>

<!-- =======================================================-->
<!DOCTYPE html>
<html>
<head>
<title>PHP Sample</title>
</head>
<body>
    <div class='user-info'>
        <h3>Clef ID: <?php echo $response['info']['id'] ?></h3>
        <h3>Name: <?php echo $info['first_name']." ".$info['last_name']?></h3>
        <h3>Email: <?php echo $info['email'] ?></h3>
    </div>
</body>
</html>

