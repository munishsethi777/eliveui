<?php 
 require_once('IConstants.inc');
 require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/MainDB.php");
 require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/M2MSite.php");
 require_once($ConstantsArray['dbServerUrl'] . "Utils/MailerUtils.php");
 require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
 require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/ChannelConfiguration.php"); 
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
     private function getChannelNumbers($value,$folderSeq){
          $CCDS = ChannelConfigurationDataStore::getInstance();
          $channels = $value->parameters; 
          $units = $value->units; 
          $flag = $CCDS->isChannelExist($folderSeq);
          if($flag){
              $chNoArr = array();
              foreach($channels as $ch){
                $chNo = $CCDS->FindChNoByFolderAndChName($folderSeq,$ch);
                if(empty($chNo)){
                    continue;
                }
                $chNoArr[$ch] = $chNo;
              }
          }else{
              $chNoArr = $this->addChannels($channels,$units,$folderSeq);     
          }
          
          return $chNoArr;
      }
      private function addChannels($channels,$units,$folderSeq){
          $i = 0;
          $chNoArr = array();
          $CCDS = ChannelConfigurationDataStore::getInstance(); 
          foreach($channels as $ch){ 
             $chConf = new ChannelConfiguration();
             $chConf->setChannelName($ch);
             $number = $i + 1;
             $chConf->setChannelNumber($number);
             $chConf->setFolderSeq($folderSeq);
             $chConf->setChannelStatusFlag(1);
             $chConf->setChannelUnit($units[$i]);
             $CCDS->Save($chConf);
             $chNoArr[$ch] = $number;
             $i++;
          }
          return $chNoArr;   
      }
      private function updateLastSyncedOnWithAdd90Min($lastSyncedDate,$folderSeq){
          $syncdate = DateTime::createFromFormat('d-m-Y H:i:s', $lastSyncedDate); 
          $syncdate = $syncdate->modify("+90 minutes");
          $d = $syncdate->format('Y-m-d H:i:s');
          $this->updateLastSyncedOn($d,$folderSeq,false);
          echo(" Updated Last sync date to :- " .$d);
          
      }
      private function getPast90MinTime(){
             $now = new DateTime();
             $now = $now->modify("-90 minutes");
             return $now;    
      }
      public function SaveSyncedWQDStackData($jsonString,$folderSeqs,$lastSynchDates){
          $arr = json_decode($jsonString);
          $fileDataObjArr = array();
          $WQDSDS = WQDStackDataStore::getInstance();
          $FDS = FolderDataStore::getInstance();  
          $message = "";
          $onlineStations = array_fill_keys(array_values($folderSeqs), 0);         
          foreach($arr as $key=>$value){
              $stationCode = $value->siteCode;    
              $folderSeq = $folderSeqs[$stationCode];
            try{
                $onlineStations[$folderSeq] = 1;
                echo("<br/><br/>Station Code :- " . $stationCode);
                var_dump($value);
                $channels = $value->parameters;
                $data = $value->data;
                $lastSyncDb = $lastSynchDates[$folderSeq];
                $count = count($data);
                echo(" Rows found :- " . $count);
                if($count == 1){
                   $now = $this->getPast90MinTime();
                   $sdate = DateTime::createFromFormat('d-m-Y H:i:s',$lastSyncDb); 
                   if($lastSyncDb == $data[0]->Key && $sdate < $now){
                       $this->updateLastSyncedOnWithAdd90Min($lastSyncDb,$folderSeq);
                       continue;   
                   }    
                }          
                $chNoArr = $this->getChannelNumbers($value,$folderSeq);
                $syncDate = "";
                $fileDataObjArr = array();
                foreach($data as $key=>$val){
                    $valueArr = $val->Value;
                    $syncdate = $val->Key;
                    $fileDataObj = $this->getFileDataObj($syncdate,$folderSeq);
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
                  $FDS->updateIsEnable($folderSeq,0); 
            }  
          }
          if(!empty($message)){
             MailerUtils::sendError($message,"Error During M2MSynchronizer");    
          }
          
          $M2MDs = M2MSynchronizerDataStore::getInstance();
          foreach($onlineStations as $folderSeq=>$isOnline){
               $FDS->updateIsOnline($folderSeq,$isOnline);
               $lastSyncDate = $lastSynchDates[$folderSeq] ;
               $sdate = DateTime::createFromFormat('d-m-Y H:i:s',$lastSyncDate);
               $now = $this->getPast90MinTime();
               if($sdate < $now){
                    $this->updateLastSyncedOnWithAdd90Min($lastSyncDate,$folderSeq);    
               }  
          }
          
      }
      
      private function getFileDataObj($synchDate,$folderSeq){
        $fileDataObj = new WQDData();
        $fileDataObj->setChecksum(0);
        $syncdate = DateTime::createFromFormat('d-m-Y H:i:s', $synchDate);
        $d = $syncdate->format('Y-m-d H:i:s');
        $fileDataObj->setDataDate($d);
        $fileDataObj->setFolderSeq($folderSeq);
        $fileDataObj->setReportNo(1);
        return  $fileDataObj;
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
            $lastSyncDateBySation[$row["folderseq"]] = $lastSynedOn;
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
     public function updateLastSyncedOn($lastSyncedOn,$folderSeq,$isDataExist = true){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$UPDATE_LAST_SYNCEDON);
            $stmt->bindValue(':lastsyncedon', $lastSyncedOn);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            if($isDataExist){
                $FDS = FolderDataStore::getInstance();
                $FDS->updateLastSyncedOn($lastSyncedOn,$folderSeq);
            }
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
