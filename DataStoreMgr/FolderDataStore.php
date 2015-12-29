<?php
   require_once('IConstants.inc');
   require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/MainDB.php");
   require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/Folder.php");
   require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/M2MSynchronizerDataStore.php");
   require_once($ConstantsArray['dbServerUrl'] . "/Utils/FilterUtil.php");


class FolderDataStore{
     private static $folderDataStore;
     private static $db;
     private static $INSERT = "INSERT INTO folder (foldername ,details,locationseq,isvisible,isenable,stationtype) VALUES(:foldername, :details, :locationseq,:isvisible,:isenable,:stationtype)";

     private static $UPDATE = "update folder set foldername = :foldername , details = :details ,locationseq = :locationseq, isvisible=:isvisible,isenable=:isenable, stationtype=:stationtype where seq = :folderseq ";
     
     private static $UPDATE_META = "update folder set category = :category ,industrycode =:industrycode, industryname = :industryname, address = :address, city = :city, state = :state, zipcode = :zipcode, latitude = :latitude, longitude = :longitude, email = :email , mobile = :mobile, stationname=:stationname, deviceid=:deviceid, vendor=:vendor, make=:make, model=:model, certificationsystem=:certificationsystem where seq = :folderseq";

     private static $SELECTALL = "SELECT folder.*, location.name as locationname,location.locationfolder as locationfolder from folder, location where folder.locationseq = location.seq and folder.seq not in (select folderseq from m2msites)";
     private static $SELECT_ALL_WITH_M2M_FOLDERS = "SELECT folder.*, location.name as locationname,location.locationfolder as locationfolder from folder, location where folder.locationseq = location.seq";
      private static $SELECT_ACTIVE = "SELECT * from folder where isenable = 1";

     private static $DELETE = "delete from folder where seq = :seq";

     private static $FIND_BY_SEQ = "select * from folder where seq = :seq";

     private static $FIND_BY_LOCATION = "select seq from folder where  foldername = :foldername and locationseq  = :locationseq ";

     private static $FIND_ALL_BY_LOCATION = "select * from folder  left JOIN folderuser ON  folder.seq = folderuser.folderseq where folder.locationseq = :locationseq and folderuser.userseq = :userseq";

     private static $FIND_ALL_BY_LOCATIONSEQ = "SELECT folder.*, location.name as locationname,location.locationfolder as locationfolder from folder, location where folder.locationseq = location.seq and folder.locationseq = :locationseq";

     private static $UPDATE_LAST_SYNCHDATE = "update folder set lastsynchedon = now() where seq = :seq";
     private static $UPDATE_LAST_SYNCEDON = "update folder set lastsynchedon = :lastsynchedon where seq = :seq";

     private statiC $UPDATE_LAST_REMINDED = "update folder set lastremindedon = now() where seq = :seq";

     private static $UPDATE_LAST_PARSED = "update folder set lastparsedon = now() where seq = :seq";
     private static $UPDATE_IS_ENABLED = "update folder set isenable=:isenable where seq = :seq";
     private static $UPDATE_IS_ONLINE = "update folder set isonline = :isonline where seq = :folderseq";   


     public function __construct(){
       self::$db = MainDB::getInstance();
     }

     public static function getInstance(){
        if (!self::$folderDataStore)
        {
            self::$folderDataStore = new FolderDataStore();
            return self::$folderDataStore;
        }
        return self::$folderDataStore;
    }
    private static function updateCurrentDateAction($SQL, $seq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(":seq",$seq);
        $stmt->execute();
   }
   public function updateLastSyncedOn($lastSyncedOn,$folderSeq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$UPDATE_LAST_SYNCEDON);
        $stmt->bindValue(':lastsynchedon', $lastSyncedOn);
        $stmt->bindValue(':seq', $folderSeq);
        $stmt->execute();
        $error = $stmt->errorInfo();
     }
   public function updateLastSynchDate($seq){
        $SQL = self::$UPDATE_LAST_SYNCHDATE;
        self::updateCurrentDateAction($SQL,$seq);
   }
   public function updateLastParseDate($seq){
        $SQL = self::$UPDATE_LAST_PARSED;
        self::updateCurrentDateAction($SQL,$seq);
   }
   public function updateLastReminderDate($seq){
        $SQL = self::$UPDATE_LAST_REMINDED;
        self::updateCurrentDateAction($SQL,$seq);
   }
   public function updateIsEnable($seq,$isEnabled){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$UPDATE_IS_ENABLED);
        $stmt->bindValue(":isenable",$isEnabled);
        $stmt->bindValue(":seq",$seq);
        $stmt->execute();
        $error = $stmt->errorInfo(); 
       
   }
   public function Save(Folder $folder){
      try{ 
          $SQL = self::$INSERT;
          $isUpdate = false;
          if($folder->getSeq() != null && $folder->getSeq()<> "" && $folder->getSeq() > 0){
             $SQL = self::$UPDATE;
             $isUpdate = true;
          }
          $conn = self::$db->getConnection();
          $stmt = $conn->prepare($SQL);
          $stmt->bindValue(':foldername', $folder->getFolderName());
          $stmt->bindValue(':locationseq', $folder->getLocationSeq());
          $stmt->bindValue(':details',$folder->getDetails());
          $stmt->bindValue(':isvisible',$folder->getIsVisible());
          $stmt->bindValue(':isenable',$folder->getIsEnable()); 
          $stmt->bindValue(':stationtype',$folder->getStationType());    
          if($isUpdate){
              $stmt->bindValue(':folderseq',$folder->getSeq());
              $seq = $folder->getSeq();
          }
          $stmt->execute();
          if(!$isUpdate){
             $seq = $conn->lastInsertId();
             $folder->setSeq($seq);    
          }
          //I will be put code here for throw exception and show on the screen
          $error = $stmt->errorInfo();
          if($error[2] <> ""){
            throw new Exception($error[2]);
          } 
      }catch(Exception $e){
          $logger = Logger::getLogger($ConstantsArray["logger"]);
          $logger->error("Error During Save Folder : - " . $e->getMessage());
      }
      
   }
   public function updateMeta($folder){
      $SQL = self::$UPDATE_META;
      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);
      $category = $folder->getCategory();
      $stmt->bindValue(':category',empty($category) ? null : $category);
      $industryCode = $folder->getIndustryCode();
      $stmt->bindValue(':industrycode',empty($industryCode) ? null : $industryCode);
      $industryName = $folder->getIndustryName();
      $stmt->bindValue(':industryname',empty($industryName) ? null : $industryName);
      $address = $folder->getAddress();
      $stmt->bindValue(':address',empty($address) ? null : $address);
      $city = $folder->getCity();
      $stmt->bindValue(':city',empty($city) ? null : $city);
      $state = $folder->getState();
      $stmt->bindValue(':state',empty($state) ? null : $state);
      $zipCode = $folder->getZipcode();
      $stmt->bindValue(':zipcode',empty($zipCode) ? null : $zipCode);
      $latitude = $folder->getLatitude();
      $stmt->bindValue(':latitude',empty($latitude) ? null : $latitude);
      $longitude = $folder->getLongitude();
      $stmt->bindValue(':longitude',empty($longitude) ? null : $longitude);
      $email = $folder->getEmail();
      $stmt->bindValue(':email',empty($email) ? null : $email);
      $mobile = $folder->getMobile();
      $stmt->bindValue(':mobile',empty($mobile) ? null : $mobile);
      $stationName = $folder->getStationName();
      $stmt->bindValue(':stationname',empty($stationName) ? null : $stationName); 
      $deviceId = $folder->getDeviceId();     
      $stmt->bindValue(':deviceid',empty($deviceId) ? null : $deviceId);
      $vendor = $folder->getVendor();
      $stmt->bindValue(':vendor',empty($vendor) ? null : $vendor);
      $make = $folder->getMake(); 
      $stmt->bindValue(':make',empty($make) ? null : $make);
      $model = $folder->getModel(); 
      $stmt->bindValue(':model',empty($model) ? null : $model);
      $certificationSystem = $folder->getCertificationsSystem();
      $stmt->bindValue(':certificationsystem',empty($certificationSystem) ? null : $certificationSystem);
      $stmt->bindValue(':folderseq',$folder->getSeq());
      $stmt->execute();
   }
   public function FindAll($isM2MShow = false){
        $conn = self::$db->getConnection();
        $sql = self::$SELECTALL;
        if($isM2MShow){
             $sql = self::$SELECT_ALL_WITH_M2M_FOLDERS;    
        }
        $stmt = $conn->prepare($sql);        
        $stmt->execute();
        $folderArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $folderObj = new Folder();
            $folderObj =  self::populateObject($row);
            $folderArray[$folderObj->getSeq()] = $folderObj;
        }
         return $folderArray;
   }
   public function FindActiveAll(){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$SELECT_ACTIVE);
        $stmt->execute();
        $folderArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $folderObj = new Folder();
            $folderObj =  self::populateObject($row);
            $folderArray[$folderObj->getSeq()] = $folderObj;
        }
         return $folderArray;
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
            $FolderObj =  self::populateObject($row);
            return $FolderObj;
       }
        public function FindByLoationSeq($locationSeq,$userSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_ALL_BY_LOCATION);
            $stmt->bindValue(':locationseq', $locationSeq);
            $stmt->bindValue(':userseq', $userSeq);
            $stmt->execute();
            $folderArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $folderObj = new Folder();
                $folderObj =  self::populateObject($row);
                $folderArray[$folderObj->getSeq()] = $folderObj;
            }
         return $folderArray;
       }
        
       
       public function FindByLocation($locationSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_ALL_BY_LOCATIONSEQ);
            $stmt->bindValue(':locationseq', $locationSeq);
            $stmt->execute();
            $folderArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $folderObj = new Folder();
                $folderObj =  self::populateObject($row);
                $folderArray[$folderObj->getSeq()] = $folderObj;
            }
         return $folderArray;
       }
       private function getTotalCount($sql){
        $conn = self::$db->getConnection();
        $query = FilterUtil::applyFilter($sql,false);
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $count = $stmt->rowCount();
        return $count;  
     } 
       public function FindJsonByLocationSeqs($locationSeqs){
            $conn = self::$db->getConnection();
            $FIND_BY_LOCATION_SEQS = "select * from folder where locationseq in($locationSeqs)";
            $query = FilterUtil::applyFilter($FIND_BY_LOCATION_SEQS);
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $arr = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                 array_push($arr,$this->getJsonArray($row));    
            }
            $mainArr["Rows"] = $arr;
            $mainArr["TotalRows"] = $this->getTotalCount($FIND_BY_LOCATION_SEQS);
            return json_encode($mainArr);
       }
       
       private function getJsonArray($row){
            $array = array();
            $array["seq"] = $row["seq"];
            $array["foldername"] = $row["foldername"];
            $statusCol = "<i class='fa fa-check-square-o'></i>";                  
            if($row["isenable"] == "0"){
               $statusCol = "<i class='fa fa-square-o'></i>";
            }
            $visibleCol = "<i  class='fa fa-eye'></i>";                  
            if($row["isvisible"] == "0"){
               $visibleCol = "<i class='fa fa-eye-slash'></i>";
            }
            $isOnlineCol = "<span class='label label-success'>Connected</span>";                  
            if($row["isonline"] == "0"){
               $isOnlineCol = "<span class='label label-danger'>Disconnected</span>";
            }
            $array["isenable"] = $statusCol;
            $array["isonline"] = $isOnlineCol;
            $array["isvisible"] = $visibleCol;
            $array["lastsynchedon"] = $row["lastsynchedon"];
            $array["lastremindedon"] = $row["lastremindedon"];
            return $array;     
       }
       
       public function FindByLocationSeqs($locationSeqs){
            $conn = self::$db->getConnection();
            $FIND_BY_LOCATION_SEQS = "select * from folder where locationseq in($locationSeqs) order by locationseq ASC";
            $stmt = $conn->prepare($FIND_BY_LOCATION_SEQS);

            $stmt->execute();
            $folderArray = Array();
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $folderObj = new Folder();
                $folderObj =  self::populateObject($row);
                $folderArray[$folderObj->getSeq()] = $folderObj;
            }
         return $folderArray;
       }
       public function FindBySeqs($seqsArr){
            $seqsStr = implode($seqsArr,",");
            $conn = self::$db->getConnection();
            $FIND_BY_SEQS = "select * from folder where seq in($seqsStr) order by seq ASC";
            $stmt = $conn->prepare($FIND_BY_SEQS);

            $stmt->execute();
            $folderArray = Array();
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $folderObj = new Folder();
                $folderObj =  self::populateObject($row);
                $folderArray[$folderObj->getSeq()] = $folderObj;
            }
         return $folderArray;
       }

       public static function populateObject($row){
           $folder = new Folder();
           $folder->setSeq($row['seq']);
           $name =  $row['foldername'];
           if($name == "raigarh"){
           	$name = "North Side Plant";
           }
           $actualName = self::getActualName($row['foldername']);
           $folder->setFolderName($name);
           $folder->setActualName($actualName);
           $folder->setLocation($row['locationname']);
           $folder->setLocationFolder($row['locationfolder']);
           $folder->setDetails($row['details']);
           $folder->setLocationSeq($row['locationseq']);
           $folder->setLastSynchedOn($row['lastsynchedon']);
           $folder->setLastParsedOn($row['lastparsedon']);
           $folder->setLastRemindedOn($row['lastremindedon']);
           $folder->setStationType($row['stationtype']);
           $folder->setStationName($row['stationname']);
           $folder->setCategory($row['category']);
           $folder->setIndustryCode($row['industrycode']);
           $folder->setIndustryName($row['industryname']);
           $folder->setAddress($row['address']);
           $folder->setCity($row['city']);
           $folder->setState($row['state']);
           $folder->setZipcode($row['zipcode']);
           $folder->setLatitude($row['latitude']);
           $folder->setLongitude($row['longitude']);
           $folder->setEmail($row['email']);
           $folder->setMobile($row['mobile']);
           $folder->setStationName($row['stationname']);
           $folder->setDeviceId($row['deviceid']);
           $folder->setVendor($row['vendor']);
           $folder->setMake($row['make']);
           $folder->setModel($row['model']);
           $folder->setIsEnable($row['isenable']);
           $folder->setIsVisible($row['isvisible']);
           $folder->setCertificationsSystem($row['certificationsystem']); 
           $m2mDs = M2MSynchronizerDataStore::getInstance();
           $m2mSite = $m2mDs->FindByFolderSeq($folder->getSeq());
           if(!empty($m2mSite)){
               $folder->setM2MCode($m2mSite->getSiteCode());
           }
           $folder->setIsOnline($row["isonline"]);
           return $folder;
       }
       private static function getActualName($name){
         if($name <> null && $name <>""){
           $folderName =  strtolower($name);
           $folderName = str_replace(" ","_",$folderName);
           return $folderName;
          }
          return null;
       }
       public function updateIsOnline($folderSeq,$isOnline){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$UPDATE_IS_ONLINE);
            $stmt->bindValue(':isonline', $isOnline);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            if($error[2] <> ""){
                throw new RuntimeException($error[2]);
            }
     }
       public function folerExistWithLocation($locationseq , $folderName){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_LOCATION);
            $stmt->bindValue(':foldername', $folderName);
            $stmt->bindValue(':locationseq', $locationseq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $seqExist = "";
            if($row){
             $seqExist =  $row['seq'];
            }
            return $seqExist;
       }
}

?>