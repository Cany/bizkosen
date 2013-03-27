<?php
Class Author {
        private $id     = "";
        private $author = "";
        private $role   = "";
        public function __toString() {
                return (string) ($this->id . "\t" . $this->author . "\t" . $this->role );
        }
        public function getId() {
            return $this->id;
        }
        public function setId($id) {
            $this->id = $id;
        }        public function getAuthor() {
            return $this->author;
        }
        public function setAuthor($author) {
            $this->author = $author;
        }
        public function getRole() {
           return $this->role;
        }
        public function setRole($role) {
           $this->role = $role;
        }
}
?>
      