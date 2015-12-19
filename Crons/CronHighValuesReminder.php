<?php

  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/MailerUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/PrescribedLimitsUtils.php");

  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
  //require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleReminderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ConfigurationDataStore.php");

  require_once($ConstantsArray['dbServerUrl'] ."/Managers/ReminderMgr.php");
  require_once($ConstantsArray['dbServerUrl'] ."/admin/configuration.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/HighValueRuleReminder.php");
  require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
  Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');
  
try{
    $HVRRDS = HighValueRuleReminderDataStore::getInstance();
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $CDS = ConfigurationDataStore::getInstance();
    $HVRDS = HighValueRuleDataStore::getInstance();
    $FDS = FolderDataStore::getInstance();
    $LDS = LocationDataStore::getInstance();
    $highValueRuleReminders = $HVRRDS->FindAll();
    $reminderMgr = ReminderMgr::getInstance();
    if(count($highValueRuleReminders) == 0){
        echo ("No reminders Found");
    }
    foreach($highValueRuleReminders as $highValueRuleReminder){
        echo ("\n<br> Reminder starting to send");
        $channelConfiguration = $CCDS->FindByFolderAndChannelNoWithSation($highValueRuleReminder->getFolderSeq(),
                    $highValueRuleReminder->getHighValueChannelNo());


        $folder = $FDS->FindBySeq($highValueRuleReminder->getFolderSeq());
        $location = $LDS->FindBySeq($folder->getLocationSeq());
        $channelName = $channelConfiguration[0]['channelname'];
        $channelUtil = $channelConfiguration[0]['channelunit'];
        $stationName = $folder->getStationName();
        if($channelConfiguration[0]['channelstation'] != null){
            $stationName =  $channelConfiguration[0]['channelstation'];
        }

        $highValueRule = $HVRDS->FindBySeq($highValueRuleReminder->getSeq());
        $highValue = $highValueRuleReminder->getHighValue();
        $emailIds = $highValueRuleReminder->getReminderEmail();
        $mobileNumber = $highValueRuleReminder->getReminderMobile();
        $subject = "High Value Notification for ". $folder->getStationName(). " of Channel - ". $channelName;
        $message = "High Value ". $highValue ." is observed for channel ". $channelName ." for station ". $folder->getFolderName();
        $alertEmmission = "Ambient";
        if($folder->getStationType() == "stack"){
            $alertEmmission = "Emission";
        }elseif($folder->getStationType == "effluent"){
            $alertEmmission = "Effluent";
        }
        $plimit = $channelConfiguration[0]["prescribedlimit"];//PrescribedLimitsUtils::getPrescribedLimit($channelName,$folder->getStationType());
        $mailMessage = "\r\n<br>ALERT :-- ". $alertEmmission;
        $mailMessage .= "\r\n<br>Industry name :--". $folder->getIndustryName() .", ". $folder->getCity() .", ". $folder->getState();
        $mailMessage .= "\r\n<br>CAT :--". $folder->getCategory();
        $mailMessage .= "\r\n<br>Location :-- ". $stationName;
        $mailMessage .= "\r\n<br>EXCEEDING PARAMETER :--". $channelName;
        $mailMessage .= "\r\n<br>VALUE :-- ". $highValue  ;
        if($plimit){
            $mailMessage .=" against Pres. Stand. ". $plimit ." ". $channelUtil ;
        }
        $mailMessage .= "\r\n<br>". date("D, d-M-Y H:i");
        $mailMessage .= "\r\n<br>Average Value for last 15 Minutes";
        $mailMessage .= "\r\n<br>Respond at cems.cpcb@nic.in";
        if($emailIds != ""){
            ReminderMgr::sendEmail($emailIds,$subject, $mailMessage);
        }
        if($mobileNumber != ""){
            $smsMessage = "SMS Alert from CPCB";
            $smsMessage .= $mailMessage;
            $smsMessage = str_replace("<br>","",$smsMessage);
            $smsMessage = str_replace("�g/m�","mg/nm3",$smsMessage);
            ReminderMgr::sendSMS($mobileNumber,$smsMessage);
        }
        echo $smsMessage;
        $HVRRDS->SentStatusChange(1,$highValueRuleReminder->getSeq());
    }// Loop

  //Daily cumulative report for sms sent
    $toDate = new DateTime();
    $fromDate = new DateTime();
    $fromDate = $fromDate->sub(new DateInterval('P1D'));

    $fromDateStr = $fromDate->format("Y/m/d  H:i:s");
    $toDateStr = $toDate->format("Y/m/d  H:i:s");
    $highValueOccurences = $HVRRDS->FindByFromToDate($fromDateStr, $toDateStr);
    $tbl = "<strong>No Reminder Found</strong>";
    if(count($highValueOccurences) > 0){
        $tbl = "<table border='1' style='border:1px silder solid'><tr><th style='text-align:left'>Name of Industry</th><th style='text-align:left'>No. of times SMSs Sent</th><th style='text-align:left'>Exceeding Parameters</th></tr>";
        foreach($highValueOccurences as $occurence){
        $tbl .="<tr><td>". $occurence['industryname'] ."</td><td>". $occurence['total'] ."</td><td>". $occurence['channelname'] ."</td></tr>";
        }
        $tbl .="</table>";    
    }
  
    echo $tbl;
    $ThatTime = strtotime("16:00:00");
    $now = time();
    if ($now >= $ThatTime) {
        $smsSummaryLastSentOn = $CDS->getConfiguration($CDS::$smsSummaryLastSentOn);  
        $smsSummaryLastSentOn = Date('Y/m/d', strtotime($smsSummaryLastSentOn));    
        if(Date('Y/m/d') > $smsSummaryLastSentOn){
            $emailTo = $CDS->getConfiguration($CDS::$cpcbEmail);
            $from = "noreply@envirotechlive.com";
            $subject = "High Value Reminder Summary";
            MailerUtils::sendMandrillEmailNotification($tbl,$subject,$from,$emailTo); 
            $date = new DateTime();
            $date =  date_format($date,"Y/m/d H:i:s");
            $CDS->saveConfig($CDS::$smsSummaryLastSentOn,$date);
            echo("Sent summary email");   
        }   
    }
}catch(Exception $e){
    $logger = Logger::getLogger($ConstantsArray["logger"]);
    $message = $e->getMessage();
    $logger->error("Error during CronHighValuesReminder " . $message);    
} 

?>