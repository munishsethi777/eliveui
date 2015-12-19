<?php
  
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/MainDB.php");
  
  class ConfigurationDataStore  {
    private static $CDataStore;
    private static $db; 
     
    public static $adminPassword = "adminPassword";
    public static $adminEmailId = "adminEmailId";
    public static $smsSummaryLastSentOn = "smsSummaryLastSentOn";
    public static $itc13GeneratorLastSynchDate = "itc13GeneratorLastSynchDate";
    public static $cpcbEmail = "cpcbEmail";
    public static $m2mWebApiUrl = "m2mWebApiUrl";
    public static $m2mWebApiUsername = "m2mWebApiUsername";
    public static $m2mWebApiPassword = "m2mWebApiPassword";
    
    
    private static $SQL = "select configvalue from configurations where configkey= :configkey"; 
    private static $UPDATE = "update configurations set configvalue= :value where configkey=:key";
  
    public function __construct(){
        self::$db = MainDB::getInstance();
    }

    public static function getInstance()
    {
        if (!self::$CDataStore)
        {
            self::$CDataStore = new ConfigurationDataStore();           
            return self::$CDataStore;
        }
        return self::$CDataStore;        
    }
    public function getConfiguration($configKey){
        $db = new MainDB();
        $conn = $db->getConnection();
        $stmt = $conn->prepare(self::$SQL);
        $stmt->bindValue(':configkey', $configKey); 
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $error = $stmt->errorInfo();
        if($error[0] == "00000"){
            return $row['configvalue'];
        }else{
            return null;  
        }
   }
   public function saveConfig($key,$value){
        $db = new MainDB();
        $conn = $db->getConnection();
        $stmt = $conn->prepare(self::$UPDATE);            
        $stmt->bindValue(':value', $value);
        $stmt->bindValue(':key', $key);  
        $stmt->execute();
        $error = $stmt->errorInfo();
   } 
    public function FindAll(){
        $conn = self::$db->getConnection();
        $SQL = "select * from configurations";
        $stmt = $conn->prepare($SQL); 
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $rows;
    }
  }
?>
