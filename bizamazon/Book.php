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
		private $Url="";
		private $Janle="";
        public function toString() {
           return (string) ($this->id ."\t" . $this->isbn . "\t" . $this->asin . "\t" .
                  $this->ean . "\t" . $this->title . "\t" . $this->image_url . "\t" .
                  $this->pubdate . "\t" . $this->price . "\t" . $this->pages . "\t" .
                  $this->publisher . "\t" . $this->binding . "\t" .
                  $this->currency_cd );
        }
        public function getId() {
            return $this->id;
        }
        public function setId($id) {
            $this->id = $id;
        }
        public function getIsbn() {
            return $this->isbn;
        }
        public function setIsbn($isbn) {
            $this->isbn = $isbn;
        }
        public function getAsin() {
            return $this->asin;
        }
        public function setAsin($asin) {
            $this->asin = $asin;
        }
        public function getEan() {
            return $this->ean;
        }
        public function setEan($ean) {
            $this->ean = $ean;
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
        public function getPubdate() {
            return $this->pubdate;
        }
        public function setPubdate($pubdate) {
            $this->pubdate = $pubdate;
        }
		public function setUrl($URL){
			$this->Url=$URL;
		}
		public function getUrl(){
			return $this->Url;
		}
		public function setJanle($Janle){
			$this->Janle=$Janle;
		}
		public function getJanle(){
			return $this->Janle;
		}
        public function getPrice() {
            return $this->price;
        }
        public function setPrice($price) {
            $this->price = $price;
        }
        public function getPages() {
            return $this->pages;
        }
        public function setPages($pages) {
            $this->pages = $pages;
        }
        public function getPublisher() {
            return $this->publisher;
        }
        public function setPublisher($publisher) {
            $this->publisher = $publisher;
        }
        public function getBinding() {
           return $this->binding;
        }
        public function setBinding($binding) {
           $this->binding = $binding;
        }
        public function getCurrency_cd() {
           return $this->currency_cd;
        }
        public function setCurrency_cd($currency_cd) {
           $this->currency_cd = $currency_cd;
        }
}
?>
   