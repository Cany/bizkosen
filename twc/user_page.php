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
   $id = (int)$_GET['id'];


   //ユーザー情報取得
   $sql = "select * from users where id = :id limit 1";
   $stmt = $dbh->prepare($sql);
   $stmt->execute(array(":id" => (int)$_GET['id']));
   $user = $stmt->fetch();
   if (!$user) {
      echo "no such user!";
      exit;
   }
   //echo h($user[profile_url]);

   //タイムライン取得
   $datas = array();
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


?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo h($user['fb_name']); ?></title>
    
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
		   		<br><br><br>
		    </div>
		    <div style="margin-top:60px">
				<h2>I am <?php echo h($_SESSION['user']['fb_name']); ?>
					<img src="<?php echo h($_SESSION['user']['fb_picture']); ?>">
					<button class="btn  btn-inverse" type="button" onclick="location.href='logout.php'">Logout</button>
				</h2>
				<p><a href="index.php">マイページへ</a></p>	
		    	<div style="font-size: 55px; text-align:center;margin-top:50px; color:#474b42;">
			    	<div class="page-header"><a href="<?php echo h($user['profile_url']) ?>" style="color:#474b42;"><?php echo h($user['fb_name']); ?></a></div>
   				 </div>
		    	</div> 
	    	</div>	
		</div>
	</div>






    <br/><br/>
    <div class="container">
		<div  id="container">
		    <!--Timeline Navigator-->
			<div class="timeline_container">
				<div class="timeline">
					<div class="plus"></div>
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
		</div>
	</div>
</body>
</html>


