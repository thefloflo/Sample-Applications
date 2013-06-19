<?php
    $DB_USER = 'root';
    $DB_PASSWORD = 'root';
    $DB_HOST = 'localhost';
    $DB_NAME = "clef_test";

    $clef_base_url='https://clef.io/api/v1/';
    $app_id='562306be5c59cc3f2da25095c05da670';
    $app_secret='9fd4e0d1e240e6f95b20a6223c3edbfc';

    if(isset($_POST['logout_token'])) {

        $postdata = http_build_query(
            array(
                'logout_token' => $_REQUEST['logout_token'],
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
        $response = file_get_contents($clef_base_url."logout", false, $context);

        $response = json_decode($response);

        if (isset($response['success']) && $response['success'] == 1 && isset($response['clef_id'])) {
            $mysql = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD);

            // log user out in the DB!
            $now = time();
            mysqli_query($mysql, "UPDATE {$DB_NAME}.users SET logged_out_at={$now} WHERE id='{$response['clef_id']}';");
        }
    }
?>