<?php
 
require_once('config.php');
 
session_start();
 
if (empty($_GET['code'])) {
    // 認証の準備
    $_SESSION['state'] = sha1(uniqid(mt_rand(), true));
    
    $params = array(
        'client_id' => APP_ID,
        'redirect_uri' => SITE_URL.'redirect.php',
        'state' => $_SESSION['state'],
        'scope' => 'user_website,friends_website'
    );
    
    $url = "https://www.facebook.com/dialog/oauth?".http_build_query($params); 
    // facebookに一旦飛ばす
    header('Location: '.$url);
    exit;
 
} else {
    // 認証後の処理
    // CSRF対策
    if ($_SESSION['state'] != $_GET['state']) {
        echo "不正な処理！";
        exit;
    }
    
    // ユーザー情報の取得
    $params = array(
        'client_id' => APP_ID,
        'client_secret' => APP_SECRET,
        'code' => $_GET['code'],
        'redirect_uri' => SITE_URL.'redirect.php'
    );
    $url = 'https://graph.facebook.com/oauth/access_token?'.http_build_query($params);

    $body = file_get_contents($url);
    parse_str($body);
    
    $url = 'https://graph.facebook.com/me?access_token='.$access_token.'&fields=name,picture,link';
    $me = json_decode(file_get_contents($url));
    //var_dump($me);
    //exit;

    // DB処理
    try {
        $dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWORD);
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
    }
    $stmt = $dbh->prepare("select * from users where fb_user_id=:user_id limit 1");
    $stmt->execute(array(":user_id"=>$me->id));
    $user = $stmt->fetch();
 
    if (empty($user)) {
        $stmt = $dbh->prepare("insert into users (fb_user_id, fb_name, fb_picture, fb_access_token, profile_url, created) values (:user_id, :name, :picture, :access_token, :profile_url, now());");
        $params = array(
            ":user_id"=>$me->id,
            ":name"=>$me->name,
            ":picture"=>$me->picture->data->url,
            ":access_token"=>$access_token,
            ":profile_url"=>$me->link
        );
        $stmt->execute($params);
        $stmt = $dbh->prepare("select * from users where id=:last_insert_id limit 1");
        $stmt->execute(array(":last_insert_id"=>$dbh->lastInsertId()));
        $user = $stmt->fetch();        
    }
    //var_dump($user);
    //exit;

    // ログイン処理
    if (!empty($user)) {
        session_regenerate_id(true);//セッションハイジャック防止
        $_SESSION['user'] = $user;
    }

    // index.php
    header('Location: '.SITE_URL);
 
}
?>