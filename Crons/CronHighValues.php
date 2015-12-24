<?php

  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
  //require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleReminderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");

  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserConfig.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Managers/ReminderMgr.php");
  require_once($ConstantsArray['dbServerUrl'] ."/admin/configuration.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");

  require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
  Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');
  
  $FolderDS = FolderDataStore::getInstance();
  $WQDDataDS = WQDDataDataStore::getInstance();
  $WQDStackDataDS = WQDStackDataStore::getInstance();
  $HVRRDS = HighValueRuleReminderDataStore::getInstance();
  $HVRDS = HighValueRuleDataStore::getInstance();
  $highValueRules = $HVRDS->FindAll();
  foreach($highValueRules as $highValueRule){
	
     echo("\n<br> Rule for station ". $highValueRule->getIndustryName(). " : " . $highValueRule->getStationName() ." (Type : - " . $highValueRule->getStationType() . ") for channel ". $highValueRule->getChannelName());
	 if($highValueRule->getIsActive() == 0){
		echo (" - skipping as inactive");
		continue;
	}
     $ruleFolderSeq =  $highValueRule->getFolderSeq();
     $folder = $FolderDS->FindBySeq($ruleFolderSeq);
     $stationType = $folder->getStationType();
     $parameter = $highValueRule->getParameter();
     $highValue = $highValueRule->getHighValue();
     $lastWQDSeq = $highValueRule->getLastRuleHitFileDataSeq();
     if($lastWQDSeq == NULL){
        $lastWQDSeq = 0;
     }
     $arr = null;
     if($stationType == "stack" || $stationType == "effluent"){
        $arr = $WQDStackDataDS->getHighValueOccurencies($ruleFolderSeq,$lastWQDSeq,$highValue, $parameter);
     }else{
        $arr = $WQDDataDS->getHighValueOccurencies($ruleFolderSeq,$lastWQDSeq,$highValue, $parameter);
     }
	 $maxWQD = $arr[0]['wqdfiledataseq'];
	 $avgValue =  $arr[0][1];
	 echo (" - Average value found :". $avgValue . " from seq ". $lastWQDSeq ." to lastSeq :- " . $maxWQD);
	 if($maxWQD != 0){
		$highValueRule->setLastRuleHitFileDataSeq($maxWQD);
	 }
	 if($avgValue > $highValue){
		echo ("\nFound high value occurence" );
		$frequency = $highValueRule->getFrequency();
		$hits = $highValueRule->getRuleHits();
		$totHits = $hits + 1;
		$highValueRule->setRuleHits($totHits);
		if($totHits >= $frequency){
			echo ("\nTotal Hits: $totHits and frequency is $frequency");
			$highValueReminder = new HighValueRuleReminder();
			$highValueReminder->setFolderSeq($ruleFolderSeq);
			$highValueReminder->setHighValue($avgValue);
			$highValueReminder->setHighValueChannelNo($parameter);
			$highValueReminder->setHighValueRuleSeq($highValueRule->getSeq());
			$highValueReminder->setReminderDate(date("Y-m-d  H:i:s"));
			$highValueReminder->setReminderEmail($highValueRule->getEmail());
			$highValueReminder->setReminderMobile($highValueRule->getMobile());
			$highValueReminder->setReminderIsSent(0);
			$highValueRule->setRuleHits(0);
			$HVRRDS->Save($highValueReminder);
			echo("\nSaved Reminder now");
		}
	 }
	 //var_dump($highValueRule);
	 $HVRDS->Save($highValueRule);
  }

?>