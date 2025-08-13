<?php
$access_token = "EAALCxdA8ZBOsBPCPi537k1gBepA2HuGlA9JR3PlSmhYwpZCLTqYYgC9eE1I2Jham9BvOSbgB6FuRBRr8xXR9gt1ZC1XZC2zmuo7Mm2CChtgUZCwp2yvpO9VxnU8pIz5zONim7NZBhhXnZCXKDBymOkM2qVJSdspfyV9PuIa4goT6TLPxtw9Ko2W2nq6nYTACe9M5qRLxUNrpwZDZD";

$url = "https://graph.facebook.com/v17.0/me/messenger_profile?access_token=$access_token";

$data = [
    "get_started" => [
        "payload" => "GET_STARTED"
    ]
];

$options = [
    "http" => [
        "header"  => "Content-type: application/json",
        "method"  => "POST",
        "content" => json_encode($data),
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo $result;
?>
