<?php
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/MainDB.php");
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/WQDData.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDDataChannelDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/FolderDataStore.php");
  
  class WQDDataDataStore  {
  
    private static $WQDDataDataStore;
    private static $db;
    private static $INSERT = "insert into wqdfiledata";
    private static $FIND_CURRENT_BY_FOLDER = "SELECT * FROM `wqdfiledata` where wqdfolderseq = :folderseq order by wqdfiledatadated DESC LIMIT 1";
    public function __construct(){
        self::$db = MainDB::getInstance();
    }

    public static function getInstance()
    {
        if (!self::$WQDDataDataStore)
        {
            self::$WQDDataDataStore = new WQDDataDataStore();           
            return self::$WQDDataDataStore;
        }
        return self::$WQDDataDataStore;        
    }
    
    
    function placeholders($text, $count=0, $separator=","){
        $result = array();
        if($count > 0){
            for($x=0; $x<$count; $x++){
                $result[] = $text;
            }
        }
        return implode($separator, $result);
   }
   public function buildBatchQuery($data,$datafield){
       $insert_values = array();
        foreach($data as $d){
         $question_marks[] = '('  . placeholders('?', sizeof($d)) . ')';
         $insert_values = array_merge($insert_values, array_values($d));
        }
       $sql = "INSERT INTO table (" . implode(",", array_keys($datafield) ) . ") VALUES " . implode(',', $question_marks);
       return $sql;
   }
   
    public function Save(WQDData $wqdData){
      $fields = "wqdfileseq,wqdfolderseq,wqdfiledatadated,wqdfiledatareportno,wqdfiledatatotalchannels,wqdfiledatachecksum,";
      $values = ":fileseq,:folderseq,:datadate,:reportno,:totalChannels,:datachecksum,";
      $conn = self::$db->getConnection();
      $WCD = WQDDataChannelDataStore::getInstance();
      $wqdChannlsArr = array();
      try{
        $wqdChannlsArr = $wqdData->getChannels();
        $SQL = self::GenerateSqlToSave($wqdChannlsArr,$fields,$values); 
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(':fileseq', 0);
        $stmt->bindValue(':folderseq', $wqdData->getFolderSeq());  
        $stmt->bindValue(':datadate',$wqdData->getDatadate());
        $stmt->bindValue(':reportno',$wqdData->getReportNo());
        $stmt->bindValue(':totalChannels',$wqdData->getTotalChannels());
        $stmt->bindValue(':datachecksum',$wqdData->getChecksum());
        $stmt->execute();
        $error = $stmt->errorInfo();
        if($error[2] <> ""){
            throw new RuntimeException($error[2]);
        }
      }catch(Exception $e){
          $message = $e->getMessage();
		  if(strpos($message, "Duplicate entry") === 0){
		 }else{
			 throw $e;
		 }
      }
     
    }
    public function GenerateSqlToSave($wqdChannlsArr,$fields,$values){
         $fieldsStr = "";
         $valueStr = "";
         $arrObj = new ArrayObject($wqdChannlsArr);        
         $it = $arrObj->getIterator();
         while( $it->valid()){
             $col = "ch"; 
             $key = $it->key();
             if(strstr($key, 'N')){
                 $key =  substr($key, 1);
             }
            $current = $it->current();
            $fields = $fields . $col .$key. "value, " . $col  . $key . "status,";
            
            //NULL CHECKS IF CHANNELS or STATUS HAVE NO VALUES(empty strings)
            $val = null;
            $sta = null;
            
            $val = $current["value"];
            $sta = $current["status"];

            if($val == ""){
                $val = "NULL";
            }
            if($sta == ""){
                $sta = "NULL";
            }
            
            
            $values =  $values . $val . "," . $sta . ",";
            $it->next();
        }
        $fields =  substr($fields, 0, strlen($fields)-1);
        $values =  substr($values, 0, strlen($values)-1); 
        $sql = self::$INSERT . " (" . $fields . ") values (". $values . ")" ;
        return $sql;   
           
    }
    public function SaveFileData($wqdDataArr,$fileSeq){
        $wqData = new WQDData();
        $count = count($wqdDataArr);
          foreach($wqdDataArr as $wqData){
              $wqData->setFileSeq($fileSeq);
              self::Save($wqData);
          }
}
    public function SaveList($wqdDataArr){
        $wqData = new WQDData();
        $syncDate = "";
        $folderSeq = "";
        foreach($wqdDataArr as $wqData){
            self::Save($wqData);
            $syncDate = $wqData->getDatadate();
            $folderSeq = $wqData->getFolderSeq();       
        }
        if(!empty($syncDate)){
             $FDS = FolderDataStore::getInstance();
             $FDS->updateLastSyncedOn($syncDate,$folderSeq);   
       }
    }
   public function getChannelsAverageInfo($folderSeq,$fromDate,$toDate,$channelsDetails){
        
        $chNames = array();
        foreach($channelsDetails as $channel){
                $chNo = $channel->getChannelNumber();
                $chName = $channel->getChannelName();
                $chUnit = $channel->getChannelUnit();
                $chNames[$chNo] = $chName;    
	}
        
        
        $sql = "SELECT *";
        $sql .= " FROM wqdfiledata where wqdfolderseq = :folderseq ";
        $sql .= " and wqdfiledatadated <= '".$toDate."' and wqdfiledatadated >= '".$fromDate."'";
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        if($rows == FALSE){
            return null;
        }
        $chValues = array();
        for($i=1;$i<=30;$i++){
            $chValues['ch'.$i] = array();
        }
        foreach($rows as $rowStd){
            $row = get_object_vars($rowStd);
            for($i=1;$i<=30;$i++){
                $chVal = (float)$row['ch'.$i.'value'];
                $chName = $chNames[$i];
                if($row['ch'.$i.'status'] == 128 || $row['ch'.$i.'status']==129){
                	if($chVal > 0 && $chVal!=985){ 
                            array_push($chValues['ch'.$i], $chVal);
                            
                        }elseif ($chVal == 985){
                        	if($chName !='PM10' && $chName!='PM2.5'){
                                	array_push($chValues['ch'.$i], $chVal);
                            	}
                        
			}elseif ($chVal <0){
                                if($chNam =='Vertical Wind Speed' ){
                                        array_push($chValues['ch'.$i],$chVal);
				}
			}
                }
            }   
        }
        $chAgg = array();
        for($i=1;$i<=30;$i++){
           $chSum = array_sum($chValues['ch'.$i]);
           $chCount = count($chValues['ch'.$i]);
           if($chCount != 0){
               $chAvg = round($chSum/$chCount,2);
               $chMin = min($chValues['ch'.$i]);
               $chMax = max($chValues['ch'.$i]);
               $chAgg['ch'.$i.'avg'] = $chAvg;
               $chAgg['ch'.$i.'max'] = $chMax;
               $chAgg['ch'.$i.'min'] = $chMin;
           }
        }

        return $chAgg;    
   }
   public function getChannelsLatestInfo($folderSeq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_CURRENT_BY_FOLDER);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->execute();
        $rows =  $stmt->fetch(PDO::FETCH_ASSOC);
        if($rows == FALSE){
            return null;
        }
        $arrObj = new ArrayObject($rows);        
        $it = $arrObj->getIterator();
        $WQDCurrentInfo = array();
        $dated = date("Y/m/d H:i:s",strtotime($rows['wqdfiledatadated'])); 
        
        $WQDCurrentInfo['formatedDated'] = date("d/Y/m H:i:s",strtotime($rows['wqdfiledatadated']));
        $WQDCurrentInfo['dated'] = $dated;
        $WQDChannelsInfo = array();
        while( $it->valid()){
             
             $key = $it->key();
             if(substr($key,0,2) == "ch"){
                 $value = $it->current();
                 $WQDChannelsInfo[$key] = $value;
             }
             $it->next();
        }
        $WQDCurrentInfo['channelsInfo'] = $WQDChannelsInfo;
        return $WQDCurrentInfo;    
   }
   public function getExportData($fromDate, $toDate, $folderSeq, $channelNos, $interval){
        $channelNosArr = explode(",", $channelNos);
        return self::getChannels($fromDate, $toDate, $folderSeq, $channelNosArr, $interval);  
  }
   
   public function getAllDataByFol($fromDate, $toDate, $folderSeq){
        $sql = "Select * from wqdfiledata where wqdfolderseq = :folderseq";
        $sql .= " and wqdfiledatadated > '" . $fromDate . "'";
        $sql .=" and wqdfiledatadated < '" . $toDate ."' order by wqdfiledatadated asc";
        try{                                                                 
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();  
            $rows = $stmt->fetchAll();
            return $rows;
        }catch(Exception $e){
             $error = $e;
        }
   }
   public function getChannels($fromDate, $toDate, $folderSeq, $channelNos, $interval){
            
        $sql = "Select wqdfiledatadated,";
        for ($i = 0, $l = count($channelNos); $i < $l; ++$i) {
            $sql .= "ch". $channelNos[$i] ."value";
            $sql .= ",ch". $channelNos[$i] ."status";
            if($i < $l-1){
                 $sql .= ",";
            }
        }
        
        $sql .= " from wqdfiledata where wqdfolderseq = :folderseq";
        $sql .= " and wqdfiledatadated >= '" . $fromDate . "'";
        $sql .=" and wqdfiledatadated <= '" . $toDate . "' and ";  
        $sql .=" (DATE_FORMAT(wqdfiledatadated,'%i') = '00' ";
        if($interval != null){
            if($interval == "5min"){
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '05' "; 
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '10' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '15' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '20' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '25' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '30' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '35' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '40' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '45' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '50' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '55' ";
            }
            if($interval == "10min"){
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '10' "; 
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '20' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '30' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '40' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '50' ";
            }
            if($interval == "15min"){
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '15' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '30' ";
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '45' ";
            }
            if($interval == "30min"){
                $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '30' ";
            }                
        }
        $sql .=")";
        $sql .=" order by wqdfiledatadated asc";
        try{                                                                 
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();  
            $rows = $stmt->fetchAll();
            return $rows;
        }catch(Exception $e){
             $error = $e;
        }  
  }
      
      
      public function getChannel($fromDate, $toDate, $folderSeq, $channelNo, $interval){
            $parms = array();
            $sql = "Select wqdfiledatadated,ch" . $channelNo . "value from wqdfiledata where wqdfolderseq = :folderseq";
            $sql .= " and wqdfiledatadated >= '" . $fromDate . "'";
            $sql .=" and wqdfiledatadated <= '" . $toDate . "' and ";  
            $sql .=" (DATE_FORMAT(wqdfiledatadated,'%i') = '00' ";
            if($interval != null){
                if($interval == "5min"){
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '05' "; 
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '10' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '15' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '20' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '25' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '30' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '35' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '40' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '45' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '50' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '55' ";
                }
                if($interval == "10min"){
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '10' "; 
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '20' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '30' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '40' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '50' ";
                }
                if($interval == "15min"){
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '15' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '30' ";
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '45' ";
                }
                if($interval == "30min"){
                    $sql .=" OR DATE_FORMAT(wqdfiledatadated,'%i') = '30' ";
                }                
            }
            $sql .=")";
            $sql .=" and (ch".$channelNo."status = 128 OR ch".$channelNo."status = 129) order by wqdfiledatadated asc";
        try{                                                                 
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();  
            $rows = $stmt->fetchAll();
            return $rows;
        }catch (Exception $e){
             $error = $e;
        }  
      }
      public function getDailyAverageValues($fromDate, $toDate, $folderSeq, $channelNo){
        $parms = array();
            $sql = "SELECT DATE_FORMAT(wqdfiledatadated,'%d/%m/%Y') as wqdfiledatadated,sum(ch" . $channelNo . "value)/count(DAY(wqdfiledatadated)) from wqdfiledata";
            $sql .= " where wqdfolderseq = :folderseq";
            $sql .= " and wqdfiledatadated >= '" . $fromDate . "'";
            $sql .= " and wqdfiledatadated <= '" . $toDate . "'";  
            $sql .= " and (ch".$channelNo."status = 128 OR ch".$channelNo."status = 129)";
            $sql .= " group by DAY(wqdfiledatadated) order by wqdfiledatadated Asc";
            
        try{                                                                 
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();  
            $rows = $stmt->fetchAll();
            return $rows;
        }catch (Exception $e){
             $error = $e;
        }  
      }
      public function getChannelSpanValues($fromDate, $toDate, $folderSeq, $channelNo){
        $parms = array();
            $sql = "Select wqdfiledatadated,ch" . $channelNo . "value from wqdfiledata where wqdfolderseq = :folderseq";
            $sql .= " and wqdfiledatadated >= '" . $fromDate . "'";
            $sql .=" and wqdfiledatadated <= '" . $toDate . "'";  
            $sql .=" and ch".$channelNo."status = 67 order by wqdfiledatadated asc";
        try{                                                                 
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();  
            $rows = $stmt->fetchAll();
            return $rows;
        }catch (Exception $e){
             $error = $e;
        }  
      }
      public function getChannelZeroCheckValues($fromDate, $toDate, $folderSeq, $channelNo){
        $parms = array();
            $sql = "Select wqdfiledatadated,ch" . $channelNo . "value from wqdfiledata where wqdfolderseq = :folderseq";
            $sql .= " and wqdfiledatadated >= '" . $fromDate . "'";
            $sql .=" and wqdfiledatadated <= '" . $toDate . "'";  
            $sql .=" and ch".$channelNo."status = 66 order by wqdfiledatadated asc";
        try{                                                                 
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();  
            $rows = $stmt->fetchAll();
            return $rows;
        }catch (Exception $e){
             $error = $e;
        }  
      }
     
      public function getReadingJsonFromArray($rows){
           $arrObj = new ArrayObject($rows);        
           $it = $arrObj->getIterator();
           $WQDChannelsInfo = array();
           $dateArr = array();
           $valArr = array(); 
           $jsonArry = array();
           while( $it->valid()){
             $key = $it->key();
                 $value = $it->current();
                 $date = new DateTime($value[0]);
                 $dateArr[$key] = "'" . $date->format("d-m H:i" ) . "'";    
                 $valArr[$key]  = $value[1];
                 $it->next();                                           
           } 

            $jsondataDate =  json_encode($dateArr);
            $josnDataValue = json_encode($valArr);
            $jsondataDate = str_replace("\"","",$jsondataDate); 
            $josnDataValue = str_replace("\"","",$josnDataValue);    
            $jsonArry[0] = $jsondataDate;
            $jsonArry[1] = $josnDataValue; 
            return $jsonArry;                            
      }
     
      public function getReadingJsonFromArrayWithPrescribedLimits($rows,$chName,$isConvertPL){
           $arrObj = new ArrayObject($rows);        
           $it = $arrObj->getIterator();
           $WQDChannelsInfo = array();
           $dateArr = array();
           $valArr = array(); 
           $jsonArry = array();
           while( $it->valid()){
             $key = $it->key();
                 $value = $it->current();
                 $date = new DateTime($value[0]);
                 $dateArr[$key] = "'" . $date->format("d-m H:i" ) . "'";    
                 if($isConvertPL == true){
                    $valArr[$key]  = ConvertorUtils::getPrescribedValue($chName,$value[1]);
                 }else{
                    $valArr[$key]  = $value[1];
                 }
                 $it->next();                                           
           } 

            $jsondataDate =  json_encode($dateArr);
            $josnDataValue = json_encode($valArr);
            $jsondataDate = str_replace("\"","",$jsondataDate); 
            $josnDataValue = str_replace("\"","",$josnDataValue);    
            $jsonArry[0] = $jsondataDate;
            $jsonArry[1] = $josnDataValue; 
            return $jsonArry;                            
      }                        
      
      public function getMaxSeq(){
        $SQL = "Select max(wqdfiledataseq) as mx from wqdfiledata";
        try{                                                                 
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($SQL);
            $stmt->execute();
            $error = $stmt->errorInfo();  
            $rows = $stmt->fetchAll();
            return $rows[0]['mx'];
        }catch (Exception $e){
             $error = $e;
        }    
      }
      public function getHighValueOccurencies($folSeq, $lastSeq, $highValue, $parameter){
          $parameter = "ch".$parameter."value";
          $SQL = "select max(wqdfiledataseq) wqdfiledataseq,TRUNCATE(avg($parameter),2) $parameter from wqdfiledata where
wqdfiledataseq > :lastSeq and wqdfolderseq = :folSeq order by wqdfiledataseq DESC";
		  //$SQL = "select wqdfiledataseq,$parameter from wqdfiledata where $parameter > :highValue and wqdfiledataseq > :lastSeq and wqdfolderseq = :folSeq order by wqdfiledataseq DESC";
           $parms = array();
        try{                                                                 
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($SQL);
            //$stmt->bindValue(':highValue', $highValue);
            $stmt->bindValue(':lastSeq', $lastSeq);
            $stmt->bindValue(':folSeq', $folSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();  
            $rows = $stmt->fetchAll();
            return $rows;
        }catch (Exception $e){
             $error = $e;
        }  


      }
      
      public function getWQDDataByLocationSeqsAndLastSeq($locationSeqs, $lastSeq, $limit){
           try{
            $conn = self::$db->getConnection();
            $SQL = "select * from wqdfiledata INNER JOIN folder ON folder.seq= wqdfiledata.wqdfolderseq and folder.locationseq in ($locationSeqs) and wqdfiledata.wqdfiledataseq > $lastSeq limit $limit";
            $stmt = $conn->prepare($SQL);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return $rows;
          }catch(Exception $e){
            $ee = $e->getMessage();
          }
      }
      public function getWQDDataByLocationSeqsAndLastSeqs($locationSeqs, $lastSeqs, $limit){
           try{
           	$conn = self::$db->getConnection();		
           	$locSeqsArr = explode(",", $locationSeqs);
    		$lastSeqsArr = explode(",", $lastSeqs);
    		$sql = "SELECT * FROM wqdfiledata INNER JOIN folder ON folder.seq = wqdfiledata.wqdfolderseq AND (";
	    	for($i=0;$i<count($locSeqsArr );$i++){
	    		if($i>0){
    				$sql .= " or";
    			}
    			$sql .= " (folder.locationseq =$locSeqsArr[$i] AND wqdfiledata.wqdfiledataseq >$lastSeqsArr[$i] )";
    		}
			
	    $sql .= ") Limit $limit";
            $stmt = $conn->prepare($sql );
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return $rows;
          }catch(Exception $e){
            $ee = $e->getMessage();
          }
      }

       public function getDatesJson($rows){
           $arrObj = new ArrayObject($rows);        
           $it = $arrObj->getIterator();
           $WQDChannelsInfo = array();
           $dateArr = array();
           
           while( $it->valid()){
             $key = $it->key();
                 $value = $it->current();
                 $date = new DateTime($value[0]);
                 $dateArr[$key] = "'" . $date->format("d-m H:i" ) . "'";    
                 $it->next();                                           
           } 

            $jsondataDate =  json_encode($dateArr);
            $jsondataDate = str_replace("\"","",$jsondataDate); 
            return $jsondataDate;
      }
      //May be Discarded now
      public function getReadingJson($rows){
          //For highChart this method will return json array otherwise it will return jsonObject
           $arrObj = new ArrayObject($rows);        
           $it = $arrObj->getIterator();
           $WQDChannelsInfo = array();
           $dateArr = array();
           $valArr = array(); 
           $jsonArry = array();
           while( $it->valid()){
             $key = $it->key();
                 $value = $it->current();
                 //$date = new DateTime($value[0]);
                 $arr[0] =  strtotime($value[0]);
                 $arr[1] = $value[1];
                 $WQDChannelsInfo[$key] = $arr;
                 $it->next();                                           
           } 
            $jsondata =  json_encode($WQDChannelsInfo);
            $jsondata = str_replace("\"","",$jsondata);     
            return $jsondata;
      }
      private static function getMinsDiff($timeBase){
            $minsDiff = 60;
            $hrs = 1;
            $posHr = strrpos($timeBase, "hour");
            $posMin = strrpos($timeBase, "min");
            if ($posHr == 1){
                if(strlen($timeBase) < 7){
                    $hrs = substr($timeBase,0,1);   
                }     
            }else if($posHr == 2){
                if(strlen($timeBase) == 7){
                    $hrs = substr($timeBase,0,2);   
                }    
            }
            $minsDiff = 60 * (int)$hrs;
            if($posMin == 1){
                if(strlen($timeBase) == 4){
                    $minsDiff = substr($timeBase,0,1);   
                }else{
                    $minsDiff = substr($timeBase,0,2); 
                }     
            }
             
            return $minsDiff;
            
      }
      //Average Calculation Methods
      
      public static function getAverageDataByDataArrayDataSlices($timeBase,$dataArray,$dateSlices,$channelsNamesArray,$valueType,$dataFinal){
          $minsDiff = self::getMinsDiff($timeBase);
          $dateSliceIndex = 0;
            $totDataForSlice = array();
            foreach($dataArray as $dateDataLong => $data){
                  $dateSliceLong = strtotime($dateSlices[$dateSliceIndex]);
                  if($dateSliceLong != false){
                    $datesDiffMinutes = round(abs($dateDataLong - $dateSliceLong) / $minsDiff,2);
                    if($datesDiffMinutes >= $minsDiff){
                        if(count($totDataForSlice) > 0){
                            $avgData = self::getChannelAverageValues($totDataForSlice,$channelsNamesArray,$valueType);
                            if($avgData != NULL){
                                $dslDated = date("d-m-Y H:i",$dateSliceLong);
                                $arr = self::appendArray($dataFinal[$dslDated],$avgData);
                                $dataFinal[$dslDated] = $arr;
                                $totDataForSlice = array();
                                $dateSliceLong += (60 * $minsDiff); //increasing by 60mins
                                $dateSliceIndex++;
                                continue;
                            }    
                        }
                        while($datesDiffMinutes > 0){//filling the lost data of hours here
                            $dat = date("d-m-Y H:i",$dateSliceLong);
                            $arr = self::appendArray($dataFinal[$dat],self::getEmptyArray(count($channelsNamesArray)));
                            $dataFinal[$dat] = $arr;
                            $totDataForSlice = array();
                            $datesDiffMinutes -= $minsDiff;
                            $dateSliceLong += (60 * $minsDiff); //increasing by 60mins
                            $dateSliceIndex++;
                        }
                    }
                    $dateDataDate = new DateTime();
                    $dateDataDate->setTimestamp($dateDataLong);
                    $dateSliceDate = new DateTime();
                    $dateSliceDate->setTimestamp($dateSliceLong);
                    
                    if($dateDataDate >= $dateSliceDate){
                        if($dateDataDate == $dateSliceDate || count($totDataForSlice) == 0){
                        //if(count($totDataForSlice) == 0 ){
                            array_push($totDataForSlice,$data);
                            
                        }
                        $avgData = self::getChannelAverageValues($totDataForSlice,$channelsNamesArray,$valueType);
                        if($avgData != NULL){
                            $dat = date("d-m-Y H:i",$dateSliceLong);
                            $arr = self::appendArray($dataFinal[$dat],$avgData);
                            $dataFinal[$dat] = $arr;
                        }
                        $totDataForSlice = array();
                        $dateSliceIndex++;
                        $data = null;  
                    }
                    if($data != null){   
                        array_push($totDataForSlice,$data);
                    }    
                    
                }
            } 
            return $dataFinal; 
        }
        private static function getExmpArray($totalElements){
            $arr = array();
            for($i=0;$i<$totalElements;$i++){
                array_push($arr,StringUtils::$exemptedString);    
            }
            return $arr; 
        }
        private static function getEmptyArray($totalElements){
            $arr = array();
            for($i=0;$i<$totalElements;$i++){
                array_push($arr,0);    
            }
            return $arr; 
        }
        private static function appendArray($parent, $child){
            if($parent == null){
                $parent = array();    
            }
            foreach($child as $key => $val){
                array_push($parent,$val);    
            }
            return $parent;        
        }
        private static function getChannelAverageValues($dateSliceDataArray,$channelNamesArr,$valueType){
            if($dateSliceDataArray == null){
                return null;
            }
            $dateSliceData = array();
            foreach($channelNamesArr as $chKe => $chName){
                $dateSliceData[$chKe] = array();    
            }
            if($dateSliceDataArray != null){
                foreach($dateSliceDataArray as $slicesKey => $eachSlicedata){
                    foreach($eachSlicedata['channelValue'] as $chKey => $eachChData){
                        
                        $eachDataPU = self::getPLValue($valueType,
                                            $eachSlicedata['channelStatuses'][$chKey],
                                            $channelNamesArr[$chKey],
                                            $eachChData );
                        //if(!is_string($eachDataPU) || $eachDataPU == StringUtils::$exemptedString){
                        //if($eachDataPU != StringUtils::$exemptedString){
                            array_push($dateSliceData[$chKey],$eachDataPU);
                        //}
                    }
                }
            }
            $avgDataArr = array();
            
            foreach($dateSliceData as $key=>$val){
                if(count($val) != 0){
                    $sumData = array_sum($val);
                    $avg = $sumData / count($val);
                    $avgDataArr[$key] = number_format($avg,2);
                    if($sumData == 0 && $val[0] === StringUtils::$exemptedString){
                        $avgDataArr[$key] = StringUtils::$exemptedString;
                    }
                }   
            }
            return $avgDataArr;   
        }
        
        private static function getPLValue($valueType, $chStatus, $chName, $data){
            $eachDataPU = "";
            if($valueType == "zero" && $chStatus == 66){
                $eachDataPU = ConvertorUtils::getPrescribedValue($chName,$data);
            }else if($valueType == "span" && $chStatus == 67){
                $eachDataPU = ConvertorUtils::getPrescribedValue($chName,$data);
            }else if($valueType == "normal"){
                if($chStatus == 128 || $chStatus == 129){
                    $eachDataPU = ConvertorUtils::getPrescribedValue($chName,$data);
                }else if($chStatus == 0 || $data == 0){
                    $eachDataPU = 0;
                }      
            }
            if(is_string($data) && $data == StringUtils::$exemptedString){
                $eachDataPU = StringUtils::$exemptedString;
            } 
            return $eachDataPU;   
        }
  }
  
?>