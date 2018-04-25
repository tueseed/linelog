<?php
session_start();
require_once("LineLoginLib.php");
function pushmsg($uid,$accToken)
{
$url = 'https://api.line.me/v2/bot/message/push';
$messages = [
           "type"=> "text",
            "text"=> "nxlkznvozvnkzlckvn"
        ];
 $data = [
            'to' => $uid,
            'messages' => [$messages]
        ];
$post = json_encode($data);
        $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $$accToken);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);


}
// กรณีต้องการตรวจสอบการแจ้ง error ให้เปิด 3 บรรทัดล่างนี้ให้ทำงาน กรณีไม่ ให้ comment ปิดไป
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 
// กรณีมีการเชื่อมต่อกับฐานข้อมูล
//require_once("dbconnect.php");
 
/// ส่วนการกำหนดค่านี้สามารถทำเป็นไฟล์ include แทนได้
define('LINE_LOGIN_CHANNEL_ID','1576551466');
define('LINE_LOGIN_CHANNEL_SECRET','e389e6701d0f047c734d9d5c1c197103');
define('LINE_LOGIN_CALLBACK_URL','https://line-log.herokuapp.com');
 
$LineLogin = new LineLoginLib(
    LINE_LOGIN_CHANNEL_ID, LINE_LOGIN_CHANNEL_SECRET, LINE_LOGIN_CALLBACK_URL);
     
if(!isset($_SESSION['ses_login_accToken_val'])){    
    $LineLogin->authorize(); 
    exit;
}
 
$accToken = $_SESSION['ses_login_accToken_val'];
// Status Token Check
if($LineLogin->verifyToken($accToken)){
    echo $accToken."<br><hr>";
    echo "Token Status OK <br>";  
}
 
 
echo "<pre>";
// Status Token Check with Result 
//$statusToken = $LineLogin->verifyToken($accToken, true);
//print_r($statusToken);
 
 
//////////////////////////
echo "<hr>";
// GET LINE USERID FROM USER PROFILE
//$userID = $LineLogin->userProfile($accToken);
//echo $userID;
 
//////////////////////////
echo "<hr>";
// GET LINE USER PROFILE 
$userInfo = $LineLogin->userProfile($accToken,true);
if(!is_null($userInfo) && is_array($userInfo) && array_key_exists('userId',$userInfo)){
    print_r($userInfo,$accToken);
}
 
//exit;
echo "<hr>";
 
if(isset($_SESSION['ses_login_userData_val']) && $_SESSION['ses_login_userData_val']!=""){
    // GET USER DATA FROM ID TOKEN
    $lineUserData = json_decode($_SESSION['ses_login_userData_val'],true);
    print_r($lineUserData); 
    echo "<hr>";
    echo "Line UserID: ".$lineUserData['sub']."<br>";
    echo "Line Display Name: ".$lineUserData['name']."<br>";
    echo '<img style="width:100px;" src="'.$lineUserData['picture'].'" /><br>';
  $uid = $lineUserData['sub'];
    echo pushmsg($uid);
}
 
 
echo "<hr>";
if(isset($_SESSION['ses_login_refreshToken_val']) && $_SESSION['ses_login_refreshToken_val']!=""){
    echo '
    <form method="post">
    <button type="submit" name="refreshToken">Refresh Access Token</button>
    </form>   
    ';  
}
if(isset($_SESSION['ses_login_refreshToken_val']) && $_SESSION['ses_login_refreshToken_val']!=""){
    if(isset($_POST['refreshToken'])){
        $refreshToken = $_SESSION['ses_login_refreshToken_val'];
        $new_accToken = $LineLogin->refreshToken($refreshToken); 
        if(isset($new_accToken) && is_string($new_accToken)){
            $_SESSION['ses_login_accToken_val'] = $new_accToken;
        }       
        $LineLogin->redirect("login_uselib.php");
    }
}
// Revoke Token
//if($LineLogin->revokeToken($accToken)){
//  echo "Logout Line Success<br>";   
//}
//
// Revoke Token with Result
//$statusRevoke = $LineLogin->revokeToken($accToken, true);
//print_r($statusRevoke);
?>
<?php
echo "<hr>";
if($LineLogin->verifyToken($accToken)){
?>
<form method="post">
<button type="submit" name="lineLogout">LINE Logout</button>
</form>
<?php }else{ ?>
<form method="post">
<button type="submit" name="lineLogin">LINE Login</button>
</form>   
<?php } ?>
<?php
if(isset($_POST['lineLogin'])){
    $LineLogin->authorize(); 
    exit;   
}
if(isset($_POST['lineLogout'])){
    unset(
        $_SESSION['ses_login_accToken_val'],
        $_SESSION['ses_login_refreshToken_val'],
        $_SESSION['ses_login_userData_val']
    );  
    echo "<hr>";
    if($LineLogin->revokeToken($accToken)){
        echo "Logout Line Success<br>";   
    }
    echo '
    <form method="post">
    <button type="submit" name="lineLogin">LINE Login</button>
    </form>   
    ';
    $LineLogin->redirect("login_uselib.php");
}
?>
