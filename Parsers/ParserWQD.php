<?php
    //require_once('IConstants.inc');      
//  require_once($ConstantsArray['dbServerUrl'] ."log4php/Logger.php"); 
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");
    
    
    class ParserWQD{
    
        public static function parseWQD($filePath,$folderSeq){
                //$content = file("../files/AQMS.WQD");
                $content = file($filePath);
                $numLines = count($content);
                $lineRow = 0;
                
                for($i=0;$i<$numLines;$i++){
                    if(trim($content[$i]) == ""){
                        
                    }else{
                        $lineRow = $i;
                        $i = $numLines;;
                    }   
                }
                
                $channelNumbers = explode(',', trim($content[$lineRow]));
                $lineRow = $lineRow +1;
                $channelNames = explode(',', trim($content[$lineRow]));
                $totalChannels = count($channelNumbers);
                
                $arrayCount = 0;
                $WQDDataArray = new ArrayObject();
                $numLines = count($content);
                echo ("\n Total Rows Found:". ($numLines - 2));
                $lineRow = $lineRow +1;
                for ($i = $lineRow; $i < $numLines; $i++) {
                        $WQDData = new WQDData();    
                        $line = trim($content[$i]);
                        $lineVals = explode(',', $line);
                        
                        $reportNo = substr($lineVals[0],0,3);
                        if($reportNo != "001"){
                            continue;
                        }
                        $dateNumeric = substr($lineVals[0],3,12);
                        //calculating first channel value
                        $channelValue = substr($lineVals[0],15);
                        $channelStatus = $lineVals[1];
                        $dated = self::DateConvert($dateNumeric);

                        $channels = array(); 
                        $varLocation = 1;
                        foreach($channelNumbers as $channel){  
                            if($varLocation != 1){
                              $channelValue = $lineVals[$varLocation];
                              $channelStatus = $lineVals[$varLocation+1];
                              $varLocation = $varLocation +2;
                            }else{
                                $varLocation++;
                            }
                            $channelInfo = array();
                            $channelInfo['value'] = $channelValue;
                            $channelInfo['status'] = $channelStatus;
                            $channels[$channel] = $channelInfo;
                        }
                        $checkSum = $lineVals[$varLocation];
                        $WQDData->setReportNo($reportNo);
                        $WQDData->setFolderSeq($folderSeq);
                        $WQDData->setDataDate($dated);
                        $WQDData->setChannels($channels);
                        $WQDData->setTotalChannels($totalChannels);
                        $WQDData->setChecksum($checkSum);
                        $WQDDataArray[$arrayCount]=$WQDData; 
                        $arrayCount = $arrayCount +1;
                    }
                
                    return $WQDDataArray;

        }
        
        public static function DateConvert($dateStr){
            
            $YY = substr($dateStr,0,2);
            $MM = substr($dateStr,2,2);
            $DD = substr($dateStr,4,2);
            $HH = substr($dateStr,6,2);
            $MN = substr($dateStr,8,2);
            $SS = substr($dateStr,10,2);
            //11 02 19 13 50 00
            return date('Y-m-d H:i:s',mktime($HH, $MN, $SS, $MM, $DD, $YY));
        }
        
    }
?>
