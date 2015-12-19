<?php
   require_once('IConstants.inc');
   require_once($ConstantsArray['dbServerUrl'] ."//BusinessObjects//HighValueRule.php");
   require_once($ConstantsArray['dbServerUrl'] . "//DataStoreMgr//WQDDataDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] . "//DataStoreMgr//WQDStackDataStore.php");
   require_once($ConstantsArray['dbServerUrl'] . "//DataStoreMgr//MainDB.php");

 class HighValueRuleDataStore{
     private static $highValueDataStore;
     private static $db;
     private static $INSERT = "insert into highvaluerule (folderseq, email, mobile, parameterchannelno, highvalue, frequency, isactive,rulehits,lastrulehitwqdfiledataseq,rulestartwqdfiledataseq) Values(:folderseq, :email, :mobile, :parameterchannelno, :highvalue, :frequency, :isactive,:rulehits,:lastrulehitwqdfiledataseq,:rulestartwqdfiledataseq)";
     private static $UPDATE = "update highvaluerule set folderseq=:folderseq, email=:email, mobile=:mobile, parameterchannelno=:parameterchannelno, highvalue=:highvalue, frequency=:frequency, isactive=:isactive,rulehits=:rulehits,lastrulehitwqdfiledataseq=:lastrulehitwqdfiledataseq where seq = :seq";
     private static $DELETE = "delete from highvaluerule where seq = :seq";
     private static $SELECTALL = "select highvaluerule.*,folder.foldername,folder.industryname,folder.stationtype,folder.stationname,channelconfiguration.channelname from highvaluerule,folder,channelconfiguration where folder.seq = highvaluerule.folderseq" ;
     private static $FIND_BY_SEQ = "select * from highvaluerule where seq = :seq";

     private static $FIND_BY_FOLDER = "select * from highvaluerule where folderseq=:folderseq";
     private static $FIND_BY_LOCATIONSEQ = "select highvaluerule.*,channelconfiguration.channelname,channelconfiguration.channelstation, folder.foldername from highvaluerule,folder,channelconfiguration where folder.seq = channelconfiguration.folderseq and channelconfiguration.channelnumber = highvaluerule.parameterchannelno and folder.seq = highvaluerule.folderseq and highvaluerule.folderseq in(select seq from folder where folder.locationseq = :locSeq)";
     private static $HITRULE = "update highvaluerule set rulehits=rulehits+1 ,lastrulehitwqdfiledataseq = :wqdseq where seq = :seq";


    public function __construct(){
       self::$db = MainDB::getInstance();
     }

    public static function getInstance(){
        if (!self::$highValueDataStore){
            self::$highValueDataStore = new HighValueRuleDataStore();
            return self::$highValueDataStore;
        }
        return self::$highValueDataStore;
    }

     public function Save(HighValueRule $highValueRule){
     try{
      $SQL = self::$INSERT;
      if($highValueRule->getSeq() != null && $highValueRule->getSeq()<> "" && $highValueRule->getSeq() > 0){
         $SQL = self::$UPDATE;
      }
      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);

      $stmt->bindValue(':folderseq', $highValueRule->getFolderSeq());
      $stmt->bindValue(':email',$highValueRule->getEmail());
      $stmt->bindValue(':mobile',$highValueRule->getMobile());
      $stmt->bindValue(':parameterchannelno',$highValueRule->getParameter());
      $stmt->bindValue(':highvalue',$highValueRule->getHighValue());
      $stmt->bindValue(':frequency',$highValueRule->getFrequency());
      $isActive = 0;
      if($highValueRule->getIsActive() == "true" || $highValueRule->getIsActive()==1){
            $isActive = 1;
      }
      $stmt->bindValue(':isactive',$isActive);
      $stmt->bindValue(':rulehits',$highValueRule->getRuleHits());
      $type = $highValueRule->getStationType();
      $maxSeq = $highValueRule->getLastRuleHitFileDataSeq();
      $seq = $highValueRule->getSeq();
      if(empty($seq)){
         if($type == "stack" || $type == "effluent"){
             $wqdsds = WQDStackDataStore::getInstance();
             $maxSeq = $wqdsds->getMaxSeq();  
          }else{
             $WQDDS = WQDDataDataStore::getInstance();
             $maxSeq = $WQDDS->getMaxSeq();   
          }    
      }
     
     $highValueRule->setLastRuleHitFileDataSeq($maxSeq);
      
      //$stmt->bindValue(':lastrulehitwqdfiledataseq',$maxSeq);
      $stmt->bindValue(':lastrulehitwqdfiledataseq',$highValueRule->getLastRuleHitFileDataSeq());
      if($SQL == self::$UPDATE){
            $stmt->bindValue(':seq',$highValueRule->getSeq());
      }else{
          $stmt->bindValue(':rulestartwqdfiledataseq',$maxSeq);
      }
      $stmt->execute();
      $error = $stmt->errorInfo();
      if($error[2] <> ""){
            throw new Exception($error[2]);
      } 
      }catch(Exception $e){
          $logger = Logger::getLogger($ConstantsArray["logger"]);
          $logger->error("Error During Save High Value Rule : - " . $e->getMessage());
      }
     }

     public function HitHighValueRule($wqdseq,$seq){
        $SQL = self::$HITRULE;
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(':wqdseq',$wqdseq);
        $stmt->bindValue(':seq',$seq);
        $stmt->execute();
        $error = $stmt->errorInfo();
     }

       public function deleteBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$DELETE);
            $stmt->bindValue(':seq', $seq);
            $stmt->execute();
            $error = $stmt->errorInfo();
        }


       public function FindBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_SEQ);
            $stmt->bindValue(':seq', $seq);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $obj =  self::populateObject($row);
            $error = $stmt->errorInfo();
            return $obj;
       }

        public function FindByFolder($folderSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_FOLDER);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objArr =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
           return $objArr;
       }

        public function FindAll(){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$SELECTALL);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
           return $objArr;
        }
        public function FindByLocationSeq($locationSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_LOCATIONSEQ);
            $stmt->bindValue(':locSeq', $locationSeq);

            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
           return $objArr;
        }
       public function FindByLocationSeqs($locationSeqs){

            $conn = self::$db->getConnection();
            $SQL = "select * from highvaluerule INNER JOIN folder ON folder.seq=highvaluerule.folderseq and folder.locationseq in ($locationSeqs)";
            $stmt = $conn->prepare($SQL);
            $stmt->execute();

            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }

           return $objArr;
        }
       public static function populateObject($row){
           $highValueRule = new HighValueRule();
           $highValueRule->setSeq($row['seq']);
           $highValueRule->setEmail($row['email']);
           $highValueRule->setFolderSeq($row['folderseq']);
           $highValueRule->setFrequency($row['frequency']);
           $highValueRule->setHighValue($row['highvalue']);
           $highValueRule->setIsActive($row['isactive']);
           $highValueRule->setMobile($row['mobile']);
           $highValueRule->setParameter($row['parameterchannelno']);
           $highValueRule->setChannelName($row['channelname']);
           $highValueRule->setChannelStation($row['channelstation']);
           $highValueRule->setFolderName($row['foldername']);
           $highValueRule->setIndustryName($row['industryname']);
           $highValueRule->setStationName($row['stationname']);
           $highValueRule->setStationType($row['stationtype']);
           $highValueRule->setRuleHits($row['rulehits']);
           $highValueRule->setLastRuleHitFileDataSeq($row['lastrulehitwqdfiledataseq']);
           $highValueRule->setRuleStartFileDataSeq($row['rulestartwqdfiledataseq']);
           return $highValueRule;
       }
 }
?>