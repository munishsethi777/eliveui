<?php
   require_once('IConstants.inc');
   require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects//HighValueRule.php");
   require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects//HighValueRuleReminder.php");
   require_once("MainDB.php");

 class HighValueRuleReminderDataStore{
     private static $highValueRuleReminderDataStore;
     private static $db;
     private static $INSERT = "insert into highvaluerulereminder (folderseq, highvalueruleseq, highvalue, highvaluechannelno, reminderdate, remindermobile, reminderemail) Values(:folderseq, :highvalueruleseq, :highvalue, :highvaluechannelno, :reminderdate, :remindermobile, :reminderemail)";
     private static $FIND_ALL_UNSENT = "select *,truncate(highvalue,1) as truncatedHighValue from highvaluerulereminder where issent = 0";
     private static $FIND_BY_FOLDER = "select *,truncate(highvalue,1) as truncatedHighValue from highvaluerulereminder where folderseq=:folSeq";
     private static $FIND_BY_FOLDER_IS_SENT = "select *,truncate(highvalue,1) as truncatedHighValue from highvaluerulereminder where folderseq=:folSeq and issent=:isSent";
     private static $SENT_STATUS_CHANGE = "update highvaluerulereminder set issent=:isSent where seq = :seq";
     private static $FIND_TOTAL_REMINDERS_BY_DATE_FOLDER = "select count(highvaluerulereminder.seq) as totalReminders, channelconfiguration.channelname from highvaluerulereminder
left join channelconfiguration on channelconfiguration.channelnumber = highvaluerulereminder.highvaluechannelno
and channelconfiguration.folderseq = highvaluerulereminder.folderseq
where highvaluerulereminder.reminderdate >= :fromDate and highvaluerulereminder.reminderdate <= :toDate and highvaluerulereminder.folderseq = :folSeq
group by channelconfiguration.channelname";

    private static $GET_HIGVALUE_LOGS = "select f.industryname,f.stationname, hr.reminderdate, hr.remindermobile, cc.channelname, hr.reminderemail,hr.highvalue from highvaluerulereminder hr inner join channelconfiguration cc on hr.highvaluechannelno = cc.channelnumber inner join folder f on hr.`folderseq` = f.seq where hr.reminderdate >= :fromDate and hr.reminderdate <= :toDate and hr.folderseq = :folSeq order by hr.reminderdate asc";

    private static $FIND_TOTAL_REMINDERS_BY_DATES = "select folder.*, count(highvaluerulereminder.seq) as total, channelconfiguration.channelname,channelconfiguration.channelstation from highvaluerulereminder
left join folder on folder.seq = highvaluerulereminder.folderseq
left join channelconfiguration on channelconfiguration.channelnumber = highvaluerulereminder.highvaluechannelno
and channelconfiguration.folderseq = folder.seq
where highvaluerulereminder.reminderdate >= :fromDate and highvaluerulereminder.reminderdate <= :toDate
group by highvaluerulereminder.highvaluechannelno, highvaluerulereminder.folderseq";
    public function __construct(){
       self::$db = MainDB::getInstance();
     }

    public static function getInstance(){
        if (!self::$highValueRuleReminderDataStore){
            self::$highValueRuleReminderDataStore = new HighValueRuleReminderDataStore();
            return self::$highValueRuleReminderDataStore;
        }
        return self::$highValueRuleReminderDataStore;
    }

     public function Save(HighValueRuleReminder $highValueRuleReminder){
         try{
              $SQL = self::$INSERT;
              $conn = self::$db->getConnection();
              $stmt = $conn->prepare($SQL);

              $stmt->bindValue(':folderseq', $highValueRuleReminder->getFolderSeq());
              $stmt->bindValue(':highvalueruleseq',$highValueRuleReminder->getHighValueRuleSeq());
              $stmt->bindValue(':highvalue',$highValueRuleReminder->getHighValue());
              $stmt->bindValue(':highvaluechannelno',$highValueRuleReminder->getHighValueChannelNo());
              $stmt->bindValue(':reminderdate',$highValueRuleReminder->getReminderDate());
              $stmt->bindValue(':remindermobile',$highValueRuleReminder->getReminderMobile());
              $stmt->bindValue(':reminderemail',$highValueRuleReminder->getReminderEmail());
              $stmt->execute();
              if($error[2] <> ""){
                throw new Exception($error[2]);
              } 
          }catch(Exception $e){
              $logger = Logger::getLogger($ConstantsArray["logger"]);
              $logger->error("Error During Save HighValueRuleReminder : - " . $e->getMessage());
          }
          
     }

     public function SentStatusChange($isSent, $seq){
        $SQL = self::$SENT_STATUS_CHANGE;
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(':isSent',$isSent);
        $stmt->bindValue(':seq',$seq);
        $stmt->execute();
        $error = $stmt->errorInfo();
     }
     public function FindAll(){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_ALL_UNSENT);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
            return $objArr;
       }
        public function FindByFolder($folderSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_FOLDER);
            $stmt->bindValue(':folSeq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
           return $objArr;
       }
       public function FindByFolderIsSent($folderSeq,$isSent){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_FOLDER_IS_SENT);
            $stmt->bindValue(':folSeq', $folderSeq);
            $stmt->bindValue(':isSent', $isSent);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
           return $objArr;
       }

       public function FindByFolderFromToDate($folderSeq, $fromDate, $toDate){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_TOTAL_REMINDERS_BY_DATE_FOLDER);
            $stmt->bindValue(':folSeq', $folderSeq);
            $stmt->bindValue(':fromDate',$fromDate);
            $stmt->bindValue(':toDate',$toDate);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $rows = $stmt->fetchAll();
            return $rows;
       }
        public function getHighValueReminderLogs($folderSeq, $fromDate, $toDate){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$GET_HIGVALUE_LOGS);
            $stmt->bindValue(':folSeq', $folderSeq);
            $stmt->bindValue(':fromDate',$fromDate);
            $stmt->bindValue(':toDate',$toDate);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $rows = $stmt->fetchAll();
            $mainArray = array();
            foreach($rows as $row){
                $array = array();
                $array["IndustryName"] = $row["industryname"];
                $array["Station"] = $row["stationname"];
                $array["Dated"] = $row["reminderdate"];
                $array["Mobile"] = $row["remindermobile"];
                $array["Email"] = $row["reminderemail"];
                $parameter = $row["channelname"];
                if(!empty($row["channelstation"])){
                    $parameter ." - ". $row["channelstation"];
                }
                $array["Parameter"] =  $parameter ; 
                $array["Highvalue"] = $row["highvalue"]; 
                array_push($mainArray,$array);
            }
           return $mainArray;
        }
     
       
       public function FindByFromToDate($fromDate,$toDate){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_TOTAL_REMINDERS_BY_DATES);
            $stmt->bindValue(':fromDate',$fromDate);
            $stmt->bindValue(':toDate',$toDate);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $rows = $stmt->fetchAll();
            return $rows;
       }

       public static function populateObject($row){
           $hvrRem = new HighValueRuleReminder();
           $hvrRem->setSeq($row['seq']);
           $hvrRem->setFolderSeq($row['folderseq']);
           $hvrRem->setHighValueRuleSeq($row['highvalueruleseq']);
           $hvrRem->setHighValue($row['highvalue']);
           if($row['truncatedHighValue']){
               $hvrRem->setHighValue($row['truncatedHighValue']);
           }
           $hvrRem->setHighValueChannelNo($row['highvaluechannelno']);
           $hvrRem->setReminderDate($row['reminderdate']);
           $hvrRem->setReminderMobile($row['remindermobile']);
           $hvrRem->setReminderEmail($row['reminderemail']);
           return $hvrRem;
       }
 }
?>
