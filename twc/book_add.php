<?php
   require_once('config.php');
   require_once("Book.php");
   require_once("Sample_ajax.php");
   
   session_start();

   function h($s) {
      return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
   }
 
 

   //DB接続
   try {
       $dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWORD);
   } catch (PDOException $e) {
       echo $e->getMessage();
       exit;
   }


   $datas = array();
   $id = (int)$_GET['id'];
   $code = $_GET['is'];

   $stmt = $dbh->prepare("select * from users where id=:user_id limit 1");
   $stmt->execute(array(":user_id"=>$id));
   $user = $stmt->fetch();
   if(empty($user)){
	echo "このIDは存在しません<br/>ユーザIDを確認してください。";
	exit;
   }
   else{

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
      

      //book_reader参照
      $stmt = $dbh->prepare("select * from book_read where user_id = :user_id and book_id = :book_id limit 1");
      $stmt->execute(array(":user_id"=>$id, ":book_id"=>$books['book_id']));
      $reader = $stmt->fetch();
      //book_readerに登録されていなかった
      if(empty($reader)){
	    $stmt = $dbh->prepare("insert into book_read (user_id, book_id) values (:user_id, :book_id)");
            $params = array(
            ":user_id" => $id,
            ":book_id" => $books['book_id']
            );
	    $stmt->execute($params);
       }

       //タイムライン更新
       $stmt = $dbh->prepare("insert into timeline (user_id, book_id, created) values (:id, :book_id, now())");
       $params = array(
            ":id" => $id,
            ":book_id" => $books['book_id']
        );
       $book_id = $books['book_id'];
       $stmt->execute($params);

       echo $books['book_name'].'を登録しました。';
       
   }


   
?>

  



<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>本追加</title>
</head>
<body>
    
</body>
</html>
