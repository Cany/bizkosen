<?php
class Book {
        private $id           = "";
        private $isbn         = "";
        private $asin         = "";
        private $ean          = "";
        private $title        = "";
        private $image_url    = "";
        private $pubdate      = "";
        private $price        = "";
        private $page         = "";
        private $publisher    = "";
        private $binding      = "";
        private $currency_cd  = "";
	private $page_url = "";
        public function toString() {
           return (string) ($this->id ."\t" . $this->isbn . "\t" . $this->asin . "\t" .
                  $this->ean . "\t" . $this->title . "\t" . $this->image_url . "\t" .
                  $this->pubdate . "\t" . $this->price . "\t" . $this->pages . "\t" .
                  $this->publisher . "\t" . $this->binding . "\t" .
                  $this->currency_cd );
        }
	public function getPage_Url(){
	    return $this->page_url;
	}public function setPage_Url($page_url){
	    $this->page_url = $page_url;
	}
	
        public function getTitle() {
            return $this->title;
        }
        public function setTitle($title) {
            $this->title = $title;
        }
        public function getImage_url() {
            return $this->image_url;
        }
        public function setImage_url($image_url) {
            $this->image_url = $image_url;
        }
        
}
?>
   



