<?php
 require_once('../IConstants.inc'); 
 require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/WQDData.php"); 
 require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/WQDChannel.php"); 
 require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/WQDFile.php");
 require_once("G:\\Webdocs\\EnvirontechLive\\httpdocs\\DataStoreMgr\\WQDFileDataStore.php");
 $date = date("Y-m-d  H:i:s");
 $$wqdChannelArr = array();
 
  
 $wqdChannel = new WQDChannel();
 $wqdChannel->setChannelName("testChannelName");
 $wqdChannel->setChannelNumber("123");
 $wqdChannel->setChannelStatus(1);
 $wqdChannel->setChannelValue(1.2);
 
 $wqdChannel1 = new WQDChannel();
 $wqdChannel1->setChannelName("testChannelName1");
 $wqdChannel1->setChannelNumber("1231");
 $wqdChannel1->setChannelStatus(11);
 $wqdChannel1->setChannelValue(1.21);
 
 $wqdChannelArr[0] = $wqdChannel;
 $wqdChannelArr[1] = $wqdChannel1;
 //<--------------------->
 $wqdChannelArr1 = array();   
 $wqdChannel2 = new WQDChannel();
 $wqdChannel2->setChannelName("testChannelName2");
 $wqdChannel2->setChannelNumber("1232");
 $wqdChannel2->setChannelStatus(12);
 $wqdChannel2->setChannelValue(1.22);
 
 $wqdChannel3 = new WQDChannel();
 $wqdChannel3->setChannelName("testChannelName3");
 $wqdChannel3->setChannelNumber("1233");
 $wqdChannel3->setChannelStatus(13);
 $wqdChannel3->setChannelValue(1.23);
 
 
 $wqdChannelArr1[0] = $wqdChannel2;
 $wqdChannelArr1[1] = $wqdChannel3; 
 //<------------------------> 
 $wqdData = new WQDData();
 $wqdDataarr = array();
 $wqdData->setChannels($wqdChannelArr);
 $wqdData->setChecksum("testCheckSum");
 $wqdData->setDataDate($date);
 $wqdData->setReportNo(1);
 $wqdData->setTotalChannels(count($wqdChannelArr));
 
 $wqdData1 = new WQDData();
 $wqdData1->setChannels($wqdChannelArr1);
 $wqdData1->setChecksum("testCheckSum1");
 $wqdData1->setDataDate($date);
 $wqdData1->setReportNo(1);
 $wqdData1->setTotalChannels(count($wqdChannelArr1));
 
 $wqdDataarr[0] = $wqdData;
 $wqdDataarr[1] = $wqdData1;
 
 
 $wqdFile = new WQDFile();
 $wqdFile->setFileDate($date);
 $wqdFile->setFolderSeq(5);
 $wqdFile->setLocationSeq(4);
 $wqdFile->setName("testFileName.txt");
 $wqdFile->setData($wqdDataarr);
 $WFDS = WQDFileDataStore::getInstance();
 $WFDS->Save($wqdFile);
 
 
  
?>
