<?php
$accessToken = 'EAAOgeWmZASQYBPAB2rJj4ZCOJVY2W17PVK9XHD3WjrcW2AuthKPK993mAkwI1rvyCVZAuatCUuQu472bKRL6ARCIQDw98SLttrzfZB0twBFHNtEbxAzPG0lnaWjtsbQoLkqhkpvZBnFZCkewnr1JO0yuqZCZCTH9qJzrathQ1o3pZBxMw4OTVbtlIyFDdzUFaJp8W0nJPEkjYFgZDZD';

$psid = ''; // From step 3

// Example dynamic values (replace with your real DB lookup)
$card_id = '1234567890';
$time_in = 'Aug 1, 2025 : 6:30am';
$time_out = 'No Time Out Yet';

$message = [
    'recipient' => [ 'id' => $psid ],
    'message' => [ 'text' => "✅ Your card {$card_id} was scanned!\n📌 Time In: {$time_in}\n📌 Time Out: {$time_out}" ]
];

$ch = curl_init('https://graph.facebook.com/v19.0/me/messages?access_token=' . $accessToken);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $response;
?>
