<?php
   require_once('config.php');
   require_once("Book.php");
   require_once("Sample_ajax.php");
   
   session_start();

   function h($s) {
      return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
   }
 
 

   //DB�ڑ�
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
	echo "����ID�͑��݂��܂���<br/>���[�UID���m�F���Ă��������B";
	exit;
   }
   else{

       //���łɂ��̖{�����邩������
       $stmt = $dbh->prepare("select * from book_list where book_code=:book_code limit 1");
       $stmt->execute(array(":book_code"=>$code));
       $books = $stmt->fetch();


       //�{���o�^����Ă��Ȃ����� 
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
      

      //book_reader�Q��
      $stmt = $dbh->prepare("select * from book_read where user_id = :user_id and book_id = :book_id limit 1");
      $stmt->execute(array(":user_id"=>$id, ":book_id"=>$books['book_id']));
      $reader = $stmt->fetch();
      //book_reader�ɓo�^����Ă��Ȃ�����
      if(empty($reader)){
	    $stmt = $dbh->prepare("insert into book_read (user_id, book_id) values (:user_id, :book_id)");
            $params = array(
            ":user_id" => $id,
            ":book_id" => $books['book_id']
            );
	    $stmt->execute($params);
       }

       //�^�C�����C���X�V
       $stmt = $dbh->prepare("insert into timeline (user_id, book_id, created) values (:id, :book_id, now())");
       $params = array(
            ":id" => $id,
            ":book_id" => $books['book_id']
        );
       $book_id = $books['book_id'];
       $stmt->execute($params);

       echo $books['book_name'].'��o�^���܂����B';
       
   }


   
?>

  



<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>�{�ǉ�</title>
</head>
<body>
    
</body>
</html>
