<?php
  ini_set('max_execution_time', 600);
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/MailerUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/DateUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWQD.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserAPPCB_HYD.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserConfig.php");
  require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."Managers/ReminderMgr.php");
  require_once($ConstantsArray['dbServerUrl'] ."admin/configuration.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserAlternateFiles.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserStackLsi.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWAD_Appcb.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/Parser_DuplicateAQMS.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserBhoomiFiles.php");
  require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
  Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');
  
  //$parserWqd = ParserWQD::parseWQD($ConstantsArray['baseUrl'].'/app/Crons/tag01102015_000108.wqd',0);
  $repositoryPath =  "/home/envirote/public_html/Repository/";
  //$repositoryPath =  $ConstantsArray['baseUrl'].'/Repository/';
  try{
  $FDS= FolderDataStore::getInstance();
  $folders = $FDS->FindAll();
  echo ("\n Cron started here");
  $fileUtils = FileSystemUtils::getInstance();
  $backupPath =  $ConstantsArray['dbServerUrl'].'Backup/';

  foreach($folders as $folder){
      if(!$folder->getIsEnable()){
          echo ("\n\n Skipping the parser as ". $folder->getActualName() ." is disabled ");
          continue;          
      }
      //$FDS->updateLastSynchDate($folder->getSeq());//UPDATE LAST SYNCH DATE
      echo ("\n\n Into Folder ". $folder->getActualName() ."(". $folder->getSeq() .")");
      $filespath = null;
      $filespath =  $repositoryPath . $folder->getLocationFolder() . "/" . $folder->getActualName();
      $files= null;
      $files= $fileUtils->Read_Directory($filespath);
      if($files == null){
           ReminderMgr::reminderInvoker($folder);
           echo ("\n No Files found in ". $filespath);
           continue;
      }

      //$FDS->updateLastParseDate($folder->getSeq());//UPDATE LAST PARSED ON DATE
      //latest file throw starts here
      $latestFile = $fileUtils->getLatestFileName($filespath);
      $latestFileType = $fileUtils->getFileType($latestFile);
      if(strtoupper($latestFileType) == "WQD"){
          copy($filespath. "/" . $latestFile ,$filespath. "/latest/latest.wqd");
          echo("\n Copied file to the latest folder");
      }
      //latest file throw ends here

      //backup Folder Creation starts
       try{
          $backupFolName =  date('MY');
          $backupFolder =  $backupPath . $folder->getLocationFolder() . "/" . $folder->getActualName() ."/". $backupFolName;
          if(!is_dir($backupFolder)){
                mkdir($backupFolder,0777,true);
          }
       }catch(Exception $e){
         echo("\n Exception occured backfolder creation -".$e->getMessage());
       }
      //backup folder creation ends..

      echo ("\n Files found in folder ". $filespath ."(".$folder->getSeq().") ->> Parsing now");
      $message = "";
      foreach($files as $value){
            //backup file in a separate location
              try{
                  $bkfilespath =  $backupFolder . "/". FileSystemUtils::getFileName($value);
                  copy($value ,$bkfilespath);
              }catch(Exception $e){
                 echo("\n Exception occured backingup file -".$e->getMessage());
              }
            //backup file ends here


            echo("\nNow Processing: ". $value);
            $fileNameTotalCount = strlen($value);
            $fileType = substr($value,$fileNameTotalCount-3,3);

            if(strtoupper($fileType)== "CFG"){
                ConfigurationParsing($value,$folder);
                continue;
            }
            if(strtoupper($fileType) == "WQD" || strtoupper($fileType) == "TXT" || strtoupper($fileType) == "WAD"
                || strtoupper($fileType) == "LSI"){
                echo ("\n". date("Y-m-d  H:i:s") ." Parsing ".$fileType." file ". $value);
                $WQDDataArray = null;
                try{
                    if(strtoupper($fileType) == "TXT" && $folder->getSeq() == 20){
                        $WQDDataArray = ParserAppcbHyd::parse($value,$folder->getSeq());
                    }elseif(strtoupper($fileType) == "TXT" && $folder->getSeq() == 9){
                        $WQDDataArray = ParserDuplicateAQMS::parse($value,$folder->getSeq());
                    }elseif(strtoupper($fileType) == "WAD" && $folder->getLocationSeq() == 10){
                        echo ("\n"."Parsing WAD of APPCB file");
                        //this case of APPCB exponential files
                        $WQDDataArray = ParserWADAPPCB::parse($value,$folder->getSeq());
                    }elseif(strtoupper($fileType) == "LSI"){
                         //this case of AMBUJA lsi files for stack folders
                        $WQDDataArray = ParserStackLsi::parse($value,$folder->getSeq());
                    }elseif(strtoupper($fileType) == "TXT" && ($folder->getSeq() == 48 || $folder->getSeq() == 49)){
                        //this case of Bhoomi instruments first parser
                        $WQDDataArray = ParserBhoomiFiles::parse($value,$folder->getSeq());

                    }else{
                        $WQDDataArray = ParserWQD::parseWQD($value,$folder->getSeq());
                    }
                    if($WQDDataArray != null){
                        if($folder->getStationType() == "stack" || $folder->getStationType() == "effluent"){
                            $WDSD = WQDStackDataStore::getInstance();
                            $WDSD->saveSyncedData($WQDDataArray);
                            echo ("\n". date("Y-m-d  H:i:s") ." Stack File Saved Successfully");
                        }else{
                            $WDD = WQDDataDataStore::getInstance();
                            $WDD->SaveList($WQDDataArray);
                            echo ("\n". date("Y-m-d  H:i:s") ." AQMS File Saved Successfully");
                            if($status != null){
                                echo ("\n Status from save call". $status);
                            }
                        }
                        unlink($value);
                        unset($WQDDataArray);
                        unset($wqdFile);
                    }
              }catch(Exception $e){
                  echo 'Exception caught while Parsing: '.  $e . "\n";
                  $to = "munishsethi777@gmail.com";
                $subject = "Exception in Elive Parsers";
                $txt = 'Exception caught while Parsing: '.  $e->getMessage(). "\n";
                $txt .= ' in folder '. $folder->getActualName();
                $from = "noreply@elive.com";
                $subject = "Exception in Elive Parsers";
                $to = "munishsethi777@gmail.com";
                $message .= $txt . "<br>";
                $logger = Logger::getLogger("myDBLogger");
                $logger->error($txt);
              }
            }
            unset($value);

      }//end of loop on files found
      if(!empty($message)){
          MailerUtils::sendMandrillEmailNotification($txt,$subject,$from,$to);
      }
      try{
        unset($folder);
        unset($files);
        unset($filespath);
      }catch (Exception $e){
        echo 'Exception caught while unset: '.  $e->getMessage(). "\n";
      }
  }
    function getFullFolderPath(){
          return $ConstantsArray['applicationURL']. "Repository/" . self::getLocationFolder() . "/" . self::getFolderName();
    }

    function ConfigurationParsing($value,$folder){
        echo("\n\n Starting with Configuration file:  ". $value);
        $configs = ParserConfig::parseConfig($value,$folder->getSeq());

        $CCDS = ChannelConfigurationDataStore::getInstance();
        $CCDS->Delete($folder->getSeq());

        foreach($configs as $config){
            $channelConfig = new ChannelConfiguration();
            $channelConfig->setFolderSeq($folder->getSeq());
            $channelConfig->setChannelNumber(trim($config[0]));
            $channelConfig->setChannelStatusFlag(trim($config[1]));
            $channelConfig->setChannelName(trim($config[2]));
            $channelConfig->setChannelUnit(trim($config[3]));
            $CCDS->Save($channelConfig);

        }
        echo ("\n Unlinking configuration file". $value);
        unlink($value);
    }    
  }catch(Exception $e){
      $logger = Logger::getLogger($ConstantsArray["logger"]);
      $logger->error("Error in CronWQD : - " . $e->getMessage());
  }
  
?>