<?php

  Class ChannelConfiguration{
     private $seq,$folderSeq,$channelNumber,$channelName,$channelStatusFlag,$channelUnit,$channelStation, $prescribedlimit;


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

     public function setChannelNumber($channelNumber_){
        $this->channelNumber = $channelNumber_;
     }
     public function getChannelNumber(){
        return $this->channelNumber;
     }

     public function setChannelName($channelName_){
        $this->channelName = $channelName_;
     }
     public function getChannelName(){
        return $this->channelName;
     }

     public function setChannelStatusFlag($channelStatusFlag_){
        $this->channelStatusFlag = $channelStatusFlag_;
     }
     public function getChannelStatusFlag(){
        return $this->channelStatusFlag;
     }

     public function setChannelUnit($channelUnit_){
        $this->channelUnit = $channelUnit_;
     }
     public function getChannelUnit(){
        return $this->channelUnit;
     }

     public function setChannelStation($val_){
        $this->channelStation = $val_;
     }
     public function getChannelStation(){
        return $this->channelStation;
     }
     
     public function setPrescribedLimit($val_){
        $this->prescribedlimit = $val_;
     }
     public function getPrescribedLimit(){
        return $this->prescribedlimit;
     }

  }
?>
