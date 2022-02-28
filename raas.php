<?php
// sets up the PHP SDK
require_once ('./GSSDK.php');
$cafile = realpath('./GSSDK.lib/cacert.pem');
GSRequest::setCAFile($cafile);

// sets up keys
// same key as provided in the javascript source
$apiKey = "<YOUR API KEY HERE>";
// application user key
$userKey = "<YOUR APP USER KEY HERE>";
// application secret key
$secretKey = "<YOUR APP SECRET KEY HERE>";

// login requested, checks if uid, uid signature
// and signature timestamp matches with keys
// in case of success, starts a server side session and sets a session uid
if ($_POST['action'] == 'login') {
	$method = "accounts.exchangeUIDSignature";
	$request = new GSRequest($apiKey, $secretKey, $method, null, true, $userKey);
	$request->setParam("UID", $_POST['UID']);
	$request->setParam("UIDSignature", $_POST['UIDSignature']);
	$request->setParam("signatureTimestamp", $_POST['signatureTimestamp']);
	$response = $request->send();
  if ($response->getErrorCode()==0) {
    if (session_start()) {
        $_SESSION['uid'] = $_POST['UID'];
        echo(json_encode(array('result' => 'success')));
    }
  }
	else {
    echo(json_encode(array('result' => 'error', 'message' => $response->getErrorMessage())));
		error_log($response->getLog());
  }
}
// logout removes clears out the session uid
elseif ($_POST['action'] == 'logout') {
  $_SESSION['uid'] = "";
  echo(json_encode(array('result' => 'success')));
}
// subscribe
elseif ($_POST['action'] == 'subscribe') {
	$method = "accounts.initRegistration";
	$request = new GSRequest($apiKey, $secretKey, $method, null, true, $userKey);
	$request->setParam("isLite", "true");
	$response = $request->send();
	if($response->getErrorCode() == 0) {
		$email = $_POST['email'];
		$regToken = $response->getString("regToken");

		$method = "accounts.setAccountInfo";
		$request = new GSRequest($apiKey, $secretKey, $method, null, true, $userKey);
		$request->setParam("regToken", $regToken);
		$request->setParam("profile", "{\"email\":\"" . $email . "\"}");
		$request->setParam("subscriptions", "{\"newsletter\":{\"email\":{\"isSubscribed\":\"true\"}}}");
		$response = $request->send();
		if($response->getErrorCode() == 0) {
			echo(json_encode(array('result' => 'success')));
		}
		else {
			echo(json_encode(array('result' => 'error', 'message' => $response->getErrorMessage())));
			error_log($response->getLog());
		}
	}
	else {
		echo(json_encode(array('result' => 'error', 'message' => $response->getErrorMessage())));
		error_log($response->getLog());
	}
}
?>
