<?php
  class HighValueRuleReminder{
       private $seq, $folderSeq, $highValueRuleSeq, $highValue, $highValueChannelNo, $reminderDate, $reminderMobile, $reminderEmail, $reminderIsSent;
      
        public function setSeq($seq_){
            $this->seq = $seq_;
        }
        public function getSeq(){
            return $this->seq;
        }
        
        public function setFolderSeq($fseq_){
            $this->folderSeq = $fseq_;
        }
        public function getFolderSeq(){
            return $this->folderSeq;
        }
        
        public function setHighValueRuleSeq($hvseq_){
            $this->highValueRuleSeq = $hvseq_;
        }
        public function getHighValueRuleSeq(){
            return $this->highValueRuleSeq;
        }
        
        public function setHighValue($highValue){
            $this->highValue = $highValue;
        }
        public function getHighValue(){
            return $this->highValue;
        }
        
        public function setHighValueChannelNo($highValueChannelNo){
            $this->highValueChannelNo = $highValueChannelNo;
        }
        public function getHighValueChannelNo(){
            return $this->highValueChannelNo;
        }
        
        public function setReminderDate($reminderDate){
            $this->reminderDate = $reminderDate;
        }
        public function getReminderDate(){
            return $this->reminderDate;
        }
        
        public function setReminderMobile($reminderMob){
            $this->reminderMobile = $reminderMob;
        }
        public function getReminderMobile(){
            return $this->reminderMobile;
        }
        
        public function setReminderEmail($reminderEmail){
            $this->reminderEmail = $reminderEmail;
        }
        public function getReminderEmail(){
            return $this->reminderEmail;
        }
        
        public function setReminderIsSent($isSent){
            $this->reminderIsSent = $isSent;
        }
        public function getReminderIsSent(){
            return $this->reminderIsSent;
        }
  }
?>

