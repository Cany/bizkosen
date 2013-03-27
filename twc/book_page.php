<?php

   require_once('config.php');
   
   session_start();

   function h($s) {
      return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
   }
 
   // ログインチェック
   if (empty($_SESSION['user'])) {
      header('Location: '.SITE_URL.'login.php');
      exit;
   }
 
 // 友達情報の取得
   //$url = "https://graph.facebook.com/me/friends?access_token=".$_SESSION['user']['facebook_access_token'];
   //$friends = json_decode(file_get_contents($url));
   //var_dump($friends);
   //exit;
 

   //DB接続
   try {
       $dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWORD);
   } catch (PDOException $e) {
       echo $e->getMessage();
       exit;
   }
   $id = (int)$_GET['book_id'];


   //本情報取得
   $sql = "select * from book_list where book_id = :id limit 1";
   $stmt = $dbh->prepare($sql);
   $stmt->execute(array(":id" => $id));
   $book = $stmt->fetch();
   if (!$book) {
      echo "no such book!";
      exit;
   }


   //ユーザー取得
   $users = array();

   $sql = "select user_id from book_read where book_id = $id";
   foreach ($dbh->query($sql) as $row) {
       //exit;

       $sql_user = "select id,fb_name,fb_picture from users where id = :id limit 1";
       $stmt = $dbh->prepare($sql_user);
       $stmt->execute(array(":id" => $row['user_id']));
       $user = $stmt->fetch();
       array_unshift($users, $user);
   }


?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Book page</title>
    
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script src="../style/jquery.masonry.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
    <script type="text/javascript" src="../style/jquery.masonry.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	 
	<link rel="stylesheet" href="../style/bizkosen_index.css" type="text/css">
	<link href="../style/css/bootstrap.min.css" rel="stylesheet">	
</head>
<body>

	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/ja_JP/all.js#xfbml=1&appId=344410085676963";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>


	<div class="container" style="margin-top:150px;">
		<div class="row">
		
			<div class="span8">
				<h3><?php echo h($book['book_name']); ?></h3><br/>
				<img src="<?php echo h($book['book_picture']); ?>"><a href="<?php echo h($book['book_url']) ?>">amazonへ</a></br>
				
			    <div class="fb-comments" data-href="http://bizkosen.tk/twc/book_page.php?book_id=<?php echo h($id); ?>" data-width="470" data-num-posts="10" style="margin-top:50px"></div>
			</div>
			
			<div class="span4">
			    <h2>I am <a href="index.php"><?php echo h($_SESSION['user']['fb_name']); ?></a>
				<button class="btn  btn-inverse" type="button" onclick="location.href='logout.php'">Logout</button>
				</h2>
		    	<h4 style="text-align : center ; font-family:'メイリオ'; marigin-top:50px;">この本を読んだユーザ一覧</h4>
		    	<pre>	<div><?php foreach ($users as $user) : ?>
		    			<a href="user_page.php?id=<?php echo h($user['id']); ?>"><img src="<?php echo h($user['fb_picture']); ?>"></a>
		    			<?php endforeach; ?></div>
		    	</pre>
		    </div>
		</div>
	</div>
	
</body>
</html>


