<?php
$input = isset($_GET["input"]) ? $_GET["input"] : "";
$iquery = explode("|", $input);
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://login.microsoftonline.com/common/oauth2/v2.0/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => "client_id=$iquery[3]&grant_type=refresh_token&refresh_token=$iquery[2]",
    CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
]);

$response = curl_exec($curl);
$dataObject = json_decode($response);

curl_setopt_array($curl, [
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/me/messages",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => ["Authorization: Bearer$dataObject->access_token"],
]);

$response = curl_exec($curl);
curl_close($curl);

header("Content-Type: application/json");

$msg_list = [];
$data = json_decode($response, true);

if (isset($data["value"])) {
    $a = 1;
    foreach ($data["value"] as $message) {
        $msg = new stdClass();
        if (isset($message["from"])) {
            $msg->sender = $message["from"]["emailAddress"]["address"];
        }
        if (isset($message["body"])) {
            $msg->htmlContent = $message["body"]["content"];
        }
        $msg->number = $a++;
        $msg->subject = $message["subject"];
        $msg->time = $message["receivedDateTime"];
        array_push($msg_list, $msg);
    }
}
$resp = new stdClass();
$resp->messages = $msg_list;
echo json_encode($resp);
?>
