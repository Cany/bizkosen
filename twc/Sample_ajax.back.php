<?php
     
 function add_book($ISBN){

     header("Cache-Control: no-cache, must-revalidate");
    //  require_once('SetBookByAmazon.php');
      //$ISBN     = $_GET["no"];
      $BookCode = 'ISBN';
     //$ISBN     = '4314005564';
      //$BookCode = 'ISBN';
      $ISBN2=$ISBN;
      require_once("/Book.php");
     require_once("/Author.php");
     define('KEYID','AKIAJDPGLWLVZGIILZMQ');
     define('AssocTag','marrnex-22');
     $aws_host = "ecs.amazonaws.jp";
     $request = 'Service=AWSECommerceService'
     . '&AWSAccessKeyId='.KEYID.'&Operation=ItemLookup'
     . '&ResponseGroup=Large&Version=2008-08-19';
    $request    .= '&IdType=' . $BookCode . '&ItemId=' . $ISBN.'&AssociateTag='. AssocTag.'&SearchIndex=Books';
    $request    .= "&Timestamp=" . str_replace('GMT','T',gmdate("Y-m-dTH:i:s")) . "Z";
    $request    = canonical_Str( $request );
    $signature = get_Signature( $request, $aws_host );
    $request = "http://" . $aws_host . "/onca/xml?" . $request . "&Signature=" . $signature;
    $response   = file_get_contents($request);
    $parsed_xml = simplexml_load_string($response);
    //echo $request;
// Get AWS 判定
    $IsValid    = $parsed_xml->Items->Request->IsValid;
    if( $IsValid != 'True' ) return -1;
//
    $Item       = $parsed_xml->Items->Item;
// Get Image URL
    $Image_URL  = $Item->MediumImage->URL;
// Get ASIN
    $ASIN       = $Item->ASIN;
// Get Book_Data
    $Attributes = $Item->ItemAttributes;
    $Binding      = $Attributes->Binding;
    $EAN          = $Attributes->EAN; 
    $ISBN         = $Attributes->ISBN;
    $Pub_Date     = $Attributes->PublicationDate;
    $Price        = $Attributes->ListPrice->Amount;
    $Currency_cd  = $Attributes->ListPrice->CurrencyCode;
    $Publisher    = $Attributes->Publisher;
    $Title        = $Attributes->Title;
    $Pages        = $Attributes->NumberOfPages;
    $Page_URL     = $Item->DetailPageURL;
// ---- Added on 22/11/08

       if      ( strlen($Pub_Date) == 7 ) $Pub_Date=$Pub_Date . '-00';
       else if ( strlen($Pub_Date) == 4 ) $Pub_Date=$Pub_Date . '-00-00';
       else if ( strlen($Pub_Date) < 4  ) $Pub_Date='0000-00-00';

//----- End Added
       $book = new Book();
       $book->setIsbn($ISBN);
       $book->setAsin($ASIN);
       $book->setEan($EAN);
       $book->setTitle($Title);
       $book->setImage_url($Image_URL);
       $book->setPubdate($Pub_Date);
       $book->setPrice($Price);
       $book->setPages($Pages);
       $book->setPublisher($Publisher);
       $book->setBinding($Binding);
       $book->setCurrency_cd($Currency_cd);
       $book->setPage_Url($Page_URL);
//
    $author_name = array();
    $ncr = 0;
  //  foreach( $Attributes->Author as $Author_name ) {
   //    $author_name[$ncr] = $Author_name;
    //   $ncr = $ncr + 1;
   // }
   // foreach( $Attributes->Creator as $Creator ) {
   //    $author_name[$ncr] = $Creator;
   //    $ncr = $ncr + 1;
   // }
//
    $author_array = array();
    for ( $i=0; $i<sizeof($author_name); $i++ ) {
       $author = new Author();
       $author->setAuthor($author_name[$i]);
       $author->setRole($author_name[$i]['Role']);
       $author_array[] = $author;
    }
//
    //$return_arguments = array();
     //  $return_arguments[] = $book;
     //  $return_arguments[] = $author_array;
//
   
      //$return = SetBookByAmazon($_GET["no"], $_GET["code"]);

    //  $book   = $return[0];
     // $author_array = $return[1];
     
     if ( !$book->getIsbn() ) {
         echo " No book found for ISBN =" . $ISBN2 . " in Amazon DB <br></br>";
         $book = NULL;
         $author_array=NULL;
         return;
      }

      $blank = " ";
      echo "<table width=500 height=170 border=0>";
     echo "<tr>";
      echo "<td width=110><img src='" . $book->getImage_url() . "'></td>";
      echo "<td width=5>" . $blank . "</td>";
      echo "<td>";
     echo "<strong>" . $book->getTitle() . "</strong><br>";
      echo $blank;
    for ($i=0; $i<sizeof($author_array); $i++) {
      if ( !$author_array[$i]->getRole() ) $role = "著";
      else                                 $role = $author_array[$i]->getRole();
       echo $author_array[$i]->getAuthor() . "(" . $role;
      if ( $i !== sizeof($author_array)-1 ) echo "),";
       else                               echo ")<br>";
      }
     echo $blank . $book->getPublisher() . "(" . $book->getPubdate() . ")<br>";
      echo $blank . $book->getBinding() . "(" . $book->getPages() . "p.)<br>";
     echo $blank . $book->getPrice() . "円<br>";
     echo $book->getPage_Url();

      echo "</td>";
      echo "</tr></table>";
      return $book;
      $book = NULL;
      $author_array=NULL;
	  
	  
}




function canonical_Str( $request ) {
   $req_split = array();
   $req_split = explode("&", $request);
   for( $i=0; $i<count($req_split); $i++) {
       $req_split[$i] = urlencode( $req_split[$i] );
       $req_split[$i] = str_replace( '%3D', '=', $req_split[$i] );
   }
   sort($req_split);
   $str_join = implode('&', $req_split );
   return $str_join;
}

function get_Signature( $request, $aws_host ) {

   $key = 'EzwjR3q/piwMh+CsEm3SYdELGGJjDAsRy9fAJZxj';

   $prep01 = 'GET';
   $prep02 = $aws_host;
   $prep03 = '/onca/xml';
   $str_to_sign = $prep01 . "\n" . $prep02 . "\n" . $prep03 . "\n". $request;

   $algo = "sha256";
   $hash_out  = hash_hmac( $algo, $str_to_sign, $key, true );
   $signature = urlencode(base64_encode($hash_out));

   return $signature;
}
	 ?>
        