<?php
  class M2MSite{
     private $seq,$folderSeq,$sitecode,$lastsyncedon;
     public function setSeq($seq_){
        $this->seq = $seq_;
     }
     public function getSeq(){
        return $this->seq;
     }

     public function setFolderSeq($folderSeq_){
        $this->folderSeq = $folderSeq_;
     }
     public function getFolderSeq(){
        return $this->folderSeq;
     } 
     
     public function setSiteCode($siteCode_){
        $this->sitecode = $siteCode_;
     }
     public function getSiteCode(){
        return $this->sitecode;
     }
     
     public function setLastSyncedOn($syncedOn_){
        $this->lastsyncedon = $syncedOn_;
     }
     public function getLastSyncedOn(){
        return $this->lastsyncedon;
     }
  }
?>
