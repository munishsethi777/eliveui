<?php 
 require_once('IConstants.inc');
 require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/MainDB.php");
 require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/M2MSite.php");
 require_once($ConstantsArray['dbServerUrl'] . "Utils/MailerUtils.php");
 require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php"); 
 require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/M2MSynchronizerDataStore.php"); 
 require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
 require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/FolderDataStore.php");
 class M2MSynchronizerDataStore{
    private static  $m2mSynchronizerDataStore;
    private static $db;
    private static $SELECTALL = "select ms.* from m2msites ms inner join folder f on ms.folderseq = f.seq where f.isenable = 1";
    private static $UPDATE_LAST_SYNCEDON = "update m2msites set lastsyncedon = :lastsyncedon where folderseq = :folderseq";
    private static $INSERT_M2MSITE = "insert into m2msites(folderseq,m2msitecode,lastsyncedon) values(:folderseq,:sitecode,:lastsyncedon)";
    private static $FIND_BY_FOLDER_SEQ = "select * from m2msites where folderseq = :folderseq";
    private static $UPDATE_BY_FOLDER_SEQ = "update m2msites set m2msitecode = :sitecode where folderseq = :folderseq";
    
   
    public function __construct(){
        self::$db = MainDB::getInstance();
    }

    public static function getInstance()
    {
        if (!self::$m2mSynchronizerDataStore)
        {
            self::$m2mSynchronizerDataStore = new M2MSynchronizerDataStore();
            return self::$m2mSynchronizerDataStore;
        }
        return self::$m2mSynchronizerDataStore;
    }
    private function getChannleNumbers($channels,$folderSeq){
          $CCDS = ChannelConfigurationDataStore::getInstance();
          $chNoArr = array();
          foreach($channels as $ch){
            $chNo = $CCDS->FindChNoByFolderAndChName($folderSeq,$ch);
            if(empty($chNo)){
                continue;
            }
            $chNoArr[$ch] = $chNo;
          }
          return $chNoArr;
      }
      private function updateLastSyncedOnWithAdd90Min($lastSyncedDate,$folderSeq){
          $syncdate = DateTime::createFromFormat('d-m-Y H:i:s', $lastSyncedDate); 
          $syncdate = $syncdate->modify("+90 minutes");
          $d = $syncdate->format('Y-m-d H:i:s');
          $this->updateLastSyncedOn($d,$folderSeq);
          echo(" Updated Last sync date to :- " .$d);
          
      }
      public function SaveSyncedWQDStackData($jsonString,$folderSeqs,$lastSynchDates){
          $arr = json_decode($jsonString);
          $fileDataObjArr = array();
          $WQDSDS = WQDStackDataStore::getInstance();
          $stationCode = "";
          $message = "";
          foreach($arr as $key=>$value){
            try{
                $stationCode = $value->siteCode;
                echo("<br/><br/>Station Code :- " . $stationCode);
                var_dump($value);
                $channels = $value->parameters;
                $data = $value->data;
                $units = $value->units;
                $folderSeq = $folderSeqs[$stationCode];
                $lastSyncDate = $lastSynchDates[$stationCode];
                $count = count($data);
                echo("<br/>Rows found :- " . $count);
                if($count == 1){
                   $sdate = DateTime::createFromFormat('d-m-Y H:i:s',$lastSyncDate); 
                   $now = new DateTime();
                   $now = $now->modify("-90 minutes");
                   if($lastSyncDate == $data[0]->Key && $sdate < $now){
                         $this->updateLastSyncedOnWithAdd90Min($lastSyncDate,$folderSeq);
                         continue;   
                   } 
                }          
                $chNoArr = $this->getChannleNumbers($channels,$folderSeq);
                $syncDate = "";
                $fileDataObjArr = array();
                foreach($data as $key=>$val){
                    $valueArr = $val->Value;
                    $syncdate = $val->Key;
                    $fileDataObj = new WQDData();
                    $fileDataObj->setChecksum(0);
                    $syncdate = DateTime::createFromFormat('d-m-Y H:i:s', $val->Key);
                    $d = $syncdate->format('Y-m-d H:i:s');
                    $fileDataObj->setDataDate($d);
                    $fileDataObj->setFolderSeq($folderSeq);
                    $fileDataObj->setReportNo(1);
                    $chValueStatusArr = array();
                    $i = 0;                          
                    foreach($channels as $ch){
                        $index = $chNoArr[$ch]; 
                        $chValueStatusArr[$index]= $valueArr[$i];
                        $i++;  
                    }
                    $fileDataObj->setChannels($chValueStatusArr);
                    array_push($fileDataObjArr,$fileDataObj);     
                }
                $WQDSDS->saveSyncedData($fileDataObjArr,true);
            }catch(Exception $e){
                  $message .= "Error During M2MSynchronizer for SiteCode " . $stationCode . " : -" . $e->getMessage() . "<br/>";
                  $logger = Logger::getLogger("myDBLogger");
                  $logger->error($message); 
            }  
          }
          if(!empty($message)){
             MailerUtils::sendError($message,"Error During M2MSynchronizer");    
          }  
      } 
     public function FindAll(){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$SELECTALL);
        $stmt->execute();
        $m2mSites = array();
        $siteCodes = array();
        $folderSeqs = array();
        $lastSyncDates = array();
        $lastSyncDateBySation = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $siteCode = $row["m2msitecode"];
            array_push($siteCodes,$siteCode);
            $lastSynedOn = Date('d-m-Y H:i:s', strtotime($row["lastsyncedon"]));
            array_push($lastSyncDates,$lastSynedOn);
            $folderSeqs[$siteCode] = $row["folderseq"];
            $syncdate = DateTime::createFromFormat('Y-m-d H:i:s', $row["lastsyncedon"]);
            $lastSyncDateBySation[$siteCode] = $lastSynedOn;
        }
        $m2mSites["siteCode"] = $siteCodes;
        $m2mSites["lastSyncDate"] = $lastSyncDates;
        $mainArr = array();
        array_push($mainArr,$m2mSites);
        array_push($mainArr,$folderSeqs);        
        array_push($mainArr,$lastSyncDateBySation);
        return $mainArr;
   }
   public function FindByFolderSeq($seq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_BY_FOLDER_SEQ);
        $stmt->bindValue(':folderseq', $seq);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $m2mSiteObj = null;
        if(!empty($row)){
            $m2mSiteObj =  self::populateObject($row);    
        }
        $error = $stmt->errorInfo();
        return $m2mSiteObj;
    }
    
    public function isAlreadyExist($seq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_BY_FOLDER_SEQ);
        $stmt->bindValue(':folderseq', $seq);
        $stmt->execute();
        $rowCount = $stmt->rowCount();
        return $rowCount > 0;
    }
     public function updateLastSyncedOn($lastSyncedOn,$folderSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$UPDATE_LAST_SYNCEDON);
            $stmt->bindValue(':lastsyncedon', $lastSyncedOn);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $FDS = FolderDataStore::getInstance();
            $FDS->updateLastSyncedOn($lastSyncedOn,$folderSeq);  
     }
    public function saveM2MSite($m2mSite){
      try{ 
          $isExists = $this->isAlreadyExist($m2mSite->getFolderSeq());
          $SQL = self::$INSERT_M2MSITE;
          $conn = self::$db->getConnection();
          $isUpdate = false;
          if($isExists){
                $SQL = self::$UPDATE_BY_FOLDER_SEQ;
          } 
          $stmt = $conn->prepare($SQL);
          $stmt->bindValue(':folderseq', $m2mSite->getFolderSeq());
          $stmt->bindValue(':sitecode',$m2mSite->getSiteCode());
          if(!$isExists){
             $stmt->bindValue(':lastsyncedon',$m2mSite->getLastSyncedOn()->date);    
          }
          $stmt->execute();  
          $error = $stmt->errorInfo(); 
          if($error[2] <> ""){
            throw new Exception($error[2]);
          } 
      }catch(Exception $e){
          $logger = Logger::getLogger($ConstantsArray["logger"]);
          $logger->error("Error During Save M2MSite : - ". $e->getMessage());
      }
    }
    public function populateObject($rsItem){
        $seq_ = $rsItem["seq"] ;
        $folderSeq = $rsItem["folderseq"];
        $siteCode = $rsItem["m2msitecode"];
        $lastSyncedOn = $rsItem["lastsyncedon"];
        
        $m2mSite = new M2MSite();
        $m2mSite->setSeq($seq_);
        $m2mSite->setLastSyncedOn($lastSyncedOn);
        $m2mSite->setSiteCode($siteCode);
        $m2mSite->setFolderSeq($folderSeq);
        
        return $m2mSite;
        
    }
 }    
?>
