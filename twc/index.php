<?php
   require_once('config.php');
   require_once("Book.php");
   require_once("Sample_ajax.php");
   
   session_start();

   function h($s) {
      return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
   }


   //var_dump($_SESSION['user']);
   //exit;

 
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


   $datas = array();
   $id = $_SESSION['user']['id'];
   $memo = $_POST['contents'];
   $code = $_POST['code'];

   //タイムライン読み込み
   $sql = "select user_id,memo,book_id,created from timeline where user_id = $id order by created desc";
      foreach ($dbh->query($sql) as $row) {
      
      if(empty($row['memo'])){
	   $book_id = $row['book_id'];
           //var_dump($row);
           $book_sql = "select * from book_list where book_id = $book_id";
	   foreach ($dbh->query($book_sql) as $ans) {
	         array_unshift($row, $ans);
	   }
      }
      
      array_push($datas, $row);
   }
   //var_dump($datas);

   //メモ投稿
   if ($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST['contents']) && empty($code)) {

       $stmt = $dbh->prepare("insert into timeline (user_id, memo, created) values (:id, :memo, now())");
       $params = array(
            ":id" => $id,
            ":memo" => $memo
        );
       $stmt->execute($params);

       //タイムライン更新
       $sql = "select memo,book_id,created from timeline where user_id = $id order by created desc limit 1";
       foreach ($dbh->query($sql) as $row) {
          array_unshift($datas, $row);
       }
   }

   //本登録
   else if ($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST['code']) && empty($memo)) {

       //すでにその本があるかを検索
       $stmt = $dbh->prepare("select * from book_list where book_code=:book_code limit 1");
       $stmt->execute(array(":book_code"=>$code));
       $books = $stmt->fetch();


       //本が登録されていなかった 
       if (empty($books)) {
          $book = add_book($code);
	  
	  $book_name = $book->getTitle();
	  $book_pic = $book->getImage_url();
	  $book_url = $book->getPage_Url();

          $stmt = $dbh->prepare("insert into book_list (book_code, book_name, book_picture, book_url) values (:book_code, :book_name, :book_pic, :book_url)");
          $params = array(
              ":book_code"=>$code,
              ":book_name"=>$book_name,
              ":book_pic"=>$book_pic,
              ":book_url"=>$book_url
          );
          $stmt->execute($params);
          $stmt = $dbh->prepare("select * from book_list where book_code = :book_code limit 1");
          $stmt->execute(array(":book_code"=>$code));
          $books = $stmt->fetch();

      }

      //book_read参照
      $stmt = $dbh->prepare("select * from book_read where user_id = :user_id and book_id = :book_id limit 1");
      $stmt->execute(array(":user_id"=>$id, ":book_id"=>$books['book_id']));
      $reader = $stmt->fetch();
      //book_readに登録されていなかった
      if(empty($reader)){
	    $stmt = $dbh->prepare("insert into book_read (user_id, book_id) values (:user_id, :book_id)");
            $params = array(
            ":user_id" => $id,
            ":book_id" => $books['book_id']
            );
	    $stmt->execute($params);
       }

       //タイムライン更新
       $stmt = $dbh->prepare("insert into timeline (user_id, book_id, created) values (:user_id, :book_id, now())");
       $params = array(
            ":user_id" => $id,
            ":book_id" => $books['book_id']
        );
       $book_id = $books['book_id'];
       $stmt->execute($params);
       

       //あまりいい方法ではないが一応動く
       $sql = "select memo,book_id,created from timeline where user_id = $id order by created desc limit 1";
       foreach ($dbh->query($sql) as $row) {

          if(empty($row['memo'])){
	      $book_id = $row['book_id'];
              //var_dump($row);
              $book_sql = "select * from book_list where book_id = $book_id";
	      foreach ($dbh->query($book_sql) as $ans) {
	         array_unshift($row, $ans);
	      }
          }

          array_unshift($datas, $row);
       }
       
   }
   


   //ユーザー一覧取得
   $users = array();
 
   $sql = "select * from users order by created desc";
   foreach ($dbh->query($sql) as $row) {
       array_push($users, $row);
   }
   
?>

  



<!DOCTYPE html>
<html lang="ja">



<head>
    <meta charset="UTF-8">
    <title>マイページ</title>
    
     <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
     <script src="../style/jquery.masonry.min.js"></script>
     <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
     <script type="text/javascript" src="../style/jquery.masonry.min.js"></script>
	 <script src="js/bootstrap.min.js"></script>
	 
	<script>
		$(document).ready(function(){
		
			
			function Arrow_Points(){
				var s = $('#container').find('.item');
				$.each(s,function(i,obj){
				var posLeft = $(obj).css("left");
				$(obj).addClass('borderclass');
				if(posLeft == "0px"){
					html = "<span class='rightCorner'></span>";
					$(obj).prepend(html);
				}else{
					html = "<span class='leftCorner'></span>";
					$(obj).prepend(html);}
				});
			}
			
			
			$('.timeline_container').mousemove(function(e){
				var topdiv=$("#containertop").height();
				var pag= e.pageY - topdiv-26;
				$('.plus').css({"top":pag +"px",
				"background":"url('../style/images/014.png')","margin-left":"1px"});
			})
			.mouseout(function(){
				$('.plus').css({"background":"url('')"});
			});

			// masonry plugin call
			$('#container').masonry({itemSelector : '.item',});
			Arrow_Points();
			
			//Mouseup textarea false
			$("#popup").mouseup(function(){
				return false
			});
			
			
			//Timeline navigator on click action 
			$(".timeline_container").click(function(e){
				var topdiv=$("#containertop").height();
				//Current Postion 
				$("#popup").css({'top':(e.pageY-topdiv-33)+'px'});
				$("#popup").fadeIn(); //Popup block show
				//Textbox focus
				$("#update").focus();}
			);
				
			
			$(".deletebox").live('click',function(){
				if(confirm("Are your sure?")){
					$(this).parent().fadeOut('slow');
					//Remove item block
					$('#container').masonry( 'remove', $(this).parent() );
					//Reload masonry plugin
					$('#container').masonry( 'reload' );
					//Hiding existing Arrows
					$('.rightCorner').hide();
					$('.leftCorner').hide();
					//Injecting fresh arrows
					Arrow_Points();
					//別になくてもいける
					$('#popup').hide();}
				return false;
			});
			

			//Textarea without editing.
			$(document).mouseup(function(){
				$('#popup').hide();
			});

		});
	</script>
	
	<link rel="stylesheet" href="../style/bizkosen_index.css" type="text/css">
	<link href="../style/css/bootstrap.min.css" rel="stylesheet">
	
</head>
<body>
	<div id="containertop">
		<div class="container">
			<div class="hero-unit" style="margin-top:50px; background-image:url('../style/img/cover1.jpg') ">
    			<h1>Tebry</h1>
    			<p>Technical book histry<br><br><br> </p>
    			<form method="POST" action="" style="font-family:'メイリオ'">
		       		 本の登録　：<input type="text" name="code" placeholder="input number">
		        	<input type="submit" value="登録する" class="btn btn-primary">
		   		</form>
		   		<br><br><br>
		    </div>
		    <div style="margin-top:60px">
	    		<!-- <p><img src="<?php echo h($_SESSION['user']['fb_picture']); ?>" class="img-rounded"></p> -->
		    	<div style="font-size: 80px; text-align:center; color:#474b42">
		    		<p><?php echo h($_SESSION['user']['fb_name']); ?></p>
		    		<button class="btn btn-large btn-inverse" type="button" onclick="location.href='logout.php'" style="margin-top:50px">Logout</button>
		    	</div> 
	    	<br><br>
	    	</div>	
		</div>
	</div>
	<br><br>
	
	<div class="container">
		<div  id="container">
		    <!--Timeline Navigator-->
			<div class="timeline_container">
				<div class="timeline">
					<div class="plus"></div>
				</div>
				<div class="item" style="text-align : center ;">
					<a href='#' class='deletebox'>
					<i class="icon-trash"></i></a>
					<div style="font-family:'メイリオ';margin-left : auto ; margin-right : auto ;">タイムラインに<br>
					メモを書き込むには<br>
					タイムラインをClickしてください<br>
					</div>
				</div>
			
				<?php foreach ($datas as $data) : ?>
				<div class="item" style="font-family:'メイリオ';text-align : center ;">
					<a href="#" class="deletebox">
						<i class=" icon-trash"></i>
					</a>
					<?php echo h($data['created']); ?><br>
					<?php if(!empty($data['memo'])) : ?>
					<?php echo h($data['memo']); ?>
					<?php else : ?>
					<tb><a href="<?php echo h($data[0]['book_url']); ?>"><?php echo h($data[0]['book_name']); ?></a><br>
					<img src="<?php echo h($data[0]['book_picture']); ?>">
					<a href="book_page.php?book_id=<?php echo h($data[0]['book_id']); ?>" style="color:#333333; font-size: 18px">Go Group<i class="icon-share"></i></a>			
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
			<div id="popup" class='shade' style="text-align : center ;">
				<form method="POST" action="">
						<div class="Popup_rightCorner" > </div>
						<div style="margin-top:10px">What is Up?
							<textarea name="contents" id='update'></textarea>
							<input type='submit' value=' Update ' id='update_button' class="btn btn-primary"/>
						</div>
				</form>
			</div>	
   		</div><br><br><br><br><br><br>
	</div>
</body>
</html>


