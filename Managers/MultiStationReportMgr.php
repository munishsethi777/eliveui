<?php
  Class MultiStationReportMgr{
        private static $multiStationReportMgr;
        public static function getInstance(){
            if (!self::$multiStationReportMgr)
            {
                self::$multiStationReportMgr = new MultiStationReportMgr();
                return self::$multiStationReportMgr;
            }
            return self::$multiStationReportMgr;
        }
        public function getRecentReport($GET){

            $DataArr = array();
            $folSeqArray = array(); //holds all folderseqs
            foreach($GET as $key=>$value){
                if(strpos($key, "channelNos_") !== false){
                    $folSeq = (float)substr($key, 11);
                    array_push($folSeqArray,$folSeq);
                }
            }
            $folStationsArr = array();
            foreach($folSeqArray as $folderSeq){
                $folStation = array();
                $FDS = FolderDataStore::getInstance();
                $folder = $FDS->FindBySeq($folderSeq);
                $LDS = LocationDataStore::getInstance();
                $location = $LDS->FindBySeq($folder->getLocationSeq());
                $WQDDataDS = WQDDataDataStore::getInstance();
                $WQDInfo = $WQDDataDS->getChannelsLatestInfo($folderSeq);
                $folStation['station'] = $folder->getFolderName();
                $folStation['location'] = $location->getLocationName();
                //dates calcualted to get avg info
                $toDate = new DateTime($WQDInfo['dated']);//current date as todate
                $fromDateClone = clone $toDate;
                $fromDate = $fromDateClone->sub(new DateInterval('P1D'));
                $toDateStr = $toDate->format("Y/m/d  H:i:s");
                $fromDateStr = $fromDate->format("Y/m/d  H:i:s");
                if($folder->getStationType() == "stack" || $folder->getStationType() == "effluent"){
                    $WQDStackDataDS = WQDStackDataStore::getInstance();
                    $WQDAvgInfo = $WQDStackDataDS->getChannelsAverageInfo($folderSeq,$fromDateStr,$toDateStr);
                }else{
                    $WQDAvgInfo = $WQDDataDS->getChannelsAverageInfo($folderSeq,$fromDateStr,$toDateStr);
                }
                $CCDS = ChannelConfigurationDataStore::getInstance();
                $channelsDetails = $CCDS->FindByFolder($folderSeq);
                $channelsData = $WQDInfo['channelsInfo'];//channel details is arrray of chNo and chValue
                $unitName = new ArrayObject();
                foreach($channelsDetails as $channel){
                    $unitName[$channel->getChannelName()] = $channel->getChannelUnit();
                }
                $channelsDataArr = array();
                foreach($channelsDetails as $channel){
                    try{
                        $channelData = array();
                        $chNo = $channel->getChannelNumber();
                        $chName = $channel->getChannelName();
                        $chUnit = $channel->getChannelUnit();
                        $chData = $channelsData['ch'. $chNo .'value'];

                        $chConvertedUnitVal = ConvertorUtils::getPrescribedUnit($chName);
                        if($chConvertedUnitVal != ""){
                            $chUnit = $chConvertedUnitVal;
                        }
                        $chUnit = ConvertorUtils::getUTF8Encoded($chUnit);
                        $chAvg = $WQDAvgInfo['ch'. $channel->getChannelNumber() .'avg'];
                        $chMin = $WQDAvgInfo['ch'. $channel->getChannelNumber() .'min'];
                        $chMax = $WQDAvgInfo['ch'. $channel->getChannelNumber() .'max'];

                        $channelData["ch". $chNo ."value"] = ConvertorUtils::getPrescribedValue($chName,$chData);
                        $channelData["ch". $chNo ."unit"] = $chUnit;
                        $channelData["ch". $chNo ."avg"] = $chAvg;
                        $channelData["ch". $chNo ."min"] = $chMin;
                        $channelData["ch". $chNo ."max"] = $chMax;
                        $channelData["ch". $chNo ."name"] = $chName;
                        array_push($channelsDataArr,$channelData);
                    }catch(Exception $e){}

                }
                $folStation['channelsData'] = $channelsDataArr;
                    $dat = new DateTime($WQDInfo['dated']);
                    $datStr = $dat->format("d-m-Y H:i");
                $folStation['dated'] = $datStr;
                array_push($folStationsArr,  $folStation);
            }
            return $folStationsArr;
        }

        public function getMultiStationReport($GET){
            $infoType = $GET['infoTypeRadio'];
            $exportType = $GET['exportTypeRadio'];
            $valueType = $GET['valueTypeRadio'];
            $folSeq = $GET['stationsSelect'];
            $fromDateStr = $GET['fromDate'];
            $toDateStr = $GET['toDate'];
            $timeBase = $GET['timeBase'];
            $timeBaseQuick = $GET['timeBaseQuick'];
            $quickReportType = $GET['quickReportType'];
            if($quickReportType != "null"){
              $timeBase = $timeBaseQuick;
            }
            if($valueType != "normal"){
              $timeBase = "5min";
            }
            $isValidated = $GET['isValidated'];
            $isAverage = $GET['isAverage'];

            $folSeqArray = array(); //holds all folderseqs
            $folChannelsArray = array();//holds selected channels by foldSeq as key
            foreach($GET as $key=>$value){
                if(strpos($key, "channelNos_") !== false){
                    $folSeq = (float)substr($key, 11);
                    array_push($folSeqArray,$folSeq);
                    $folChannelsArray[$folSeq] = $value;
                }
            }
            //a few validation checks
            if(count($folChannelsArray)==0){
                return null;
            }
            if ($quickReportType == "null" &&  (false === strtotime($fromDateStr) || false === strtotime($toDateStr ))){
                return null;
            }

            //get folderNames from DB
            $FDS = FolderDataStore::getInstance();
            $foldersObjArray = $FDS->FindBySeqs($folSeqArray);
            //calculating from to dates from provided $_GET
            $fromToDates = DateUtils::getDatesArrayForStationReports($quickReportType, $fromDateStr, $toDateStr);
            //calculating Date Slices now from DateUtils
            $fromDate = new DateTime($fromToDates['fromDate']);
            $toDate = new DateTime($fromToDates['toDate']);
            $folChannelsNamesArray = array();//holds selected channels by foldSeq as key
            $allChannelsArray = array();
            $channelUnitArray = array();
            foreach($folChannelsArray as $folSeq => $folChannelChannelsArr){
                 $folName = $foldersObjArray[$folSeq]->getFolderName();
                 $CCDS = ChannelConfigurationDataStore::getInstance();
                 $ChannelsInfo = $CCDS->FindByFolderAndChannelNos($folSeq,$folChannelChannelsArr);
                 $channelNamesArr = array();
                 foreach($ChannelsInfo as $chInfo){
                     if($valueType != "normal"){
                        if(!self::isZeroSpanChannel($chInfo->getChannelName())){
                            $chNo = $chInfo->getChannelNumber();
                            $chArr = $folChannelsArray[$folSeq];
                            if(($key = array_search($chNo, $chArr)) !== false) {
                                unset($folChannelsArray[$folSeq][$key]);
                                $folChannelsArray[$folSeq] = array_values($folChannelsArray[$folSeq]);
                            }
                            continue;
                        }
                     }
                     $chUnit = $chInfo->getChannelUnit();
                     if(ConvertorUtils::getPrescribedUnit($chInfo->getChannelName())!= null){
                       $chUnit = ConvertorUtils::getPrescribedUnit($chInfo->getChannelName());
                     }
                     $chUnit = mb_check_encoding($chUnit, 'UTF-8') ? $chUnit : utf8_encode($chUnit);
                     $chStation = $chInfo->getChannelStation();
                     if(!empty($chStation)){
                        $chStation = " - " . $chStation;  
                     }
                     $chStr = $folName .'<br>'. $chInfo->getChannelName() . $chStation .'<br>'. $chUnit;   
                     array_push($allChannelsArray,$chStr);
                     array_push($channelUnitArray,$chUnit);
                     array_push($channelNamesArr,$chInfo->getChannelName());
                 }
                 $folChannelsNamesArray[$folSeq] = $channelNamesArr;
            }//Created an array with folSeq and seelcted channels for it

            $folChannelDataArray = array();
            foreach($folSeqArray as $folSeq){
                $channelNoArray = $folChannelsArray[$folSeq];
                $EDS = ExemptionDataStore::getInstance();
                $exemptions = $EDS->FindExemptionsByFolderDatesChannels($folSeq, $fromToDates['fromDate'],$fromToDates['toDate'],implode(', ',$channelNoArray));
                //Stack or AQMS
                $stationType = $foldersObjArray[$folSeq]->getStationType();
                $dataJson = self::getDataJSON($fromToDates['fromDate'],$fromToDates['toDate']
                            ,$folSeq,"5min",$channelNoArray,$periodType,$exemptions,$stationType);

                //$avgData = self::getAverageData($fromToDates['fromDate'],$fromToDates['toDate'],$folSeq,$channelNoArray);
                $folChannelDataArray[$folSeq] = $dataJson['data'];
            }//Created an array with folSeq -> date->chanelvalues

            $CMD = CommentsDataStore::getInstance();
            $commentsObjs = $CMD->FindCommentsMasterByFolChannel($folSeqArray[0],$folChannelsArray[$folSeqArray[0]]);
            $dataSlices = null;
            //if its stack type we are supporting single station/folder data only
            $stationType = $foldersObjArray[$folSeqArray[0]]->getStationType();
            if($stationType=='stack' || $stationType=='effluent'){
                //if($isAverage == "on"){
                    //$dateSlices = DateUtils::getDateSlicesByInterval($fromDate,$toDate,$timeBase);
                //}else{
                    if($timeBase == "instant"){
                        $dateSlices = DateUtils::getDateSlicesForData($folChannelDataArray[$folSeqArray[0]],$isAverage,$timeBase);
                    }else{
                        $dateSlices = DateUtils::getDateSlicesByInterval($fromDate,$toDate,$timeBase);
                    }
                //}

            }else{
                $dateSlices = DateUtils::getDateSlicesByInterval($fromDate,$toDate,$timeBase);
            }

            //$dateSlices = $dateSlicesValid;
            $dataFinal = self::getFinalDataByValueType($timeBase,$dateSlices,$folSeqArray,$folChannelDataArray,$folChannelsNamesArray,$valueType,$isAverage,$stationType);
            if($isValidated == "on"){
                $dateSlicesValidByChannelNumber = DateUtils::getDateSlicesByComments($commentsObjs,$timeBase,$folChannelsArray[$folSeqArray[0]]);
                $dataFinal = self::getValidatedDataOnly($dataFinal, $dateSlicesValidByChannelNumber);
            }
            if($valueType != "normal"){
                $dataFinal = self::getZeroSpanValues($dataFinal);
            }
            $channelDataJSON = array();
            $channelDataJSON['channels'] =  $allChannelsArray;//all channels with key as chno and val as chname
            $channelDataJSON['data'] =  $dataFinal;//dateFinal is date as key and array of channel values as value
            $finalDataJSON = self::processDataJsonForGridGraph($channelDataJSON,$infoType);
            $finalDataJSON["units"] = $channelUnitArray;
            if($infoType == "export"){
               ExportUtils::exportMultiStationGridReport($finalDataJSON,$exportType);
               return null;
            }else{
                if(count($channelDataJSON['channels']) == 1){
                    $chName = $channelNamesArr[0];
                    $presLimit = PrescribedLimitsUtils::getPrescribedLimit($chName);
                    if($presLimit != null){
                        $finalDataJSON['presLimit'] = $presLimit;
                    }
                }

                return self::getAvgMinMaxValues($finalDataJSON);
            }

        }

        private  function processDataJsonForGridGraph($dataJson,$infoType){
            $processedJson = null;
            if($infoType == "grid"  || $infoType == "export"){
                $processedJson = $dataJson;
            }elseif($infoType == "graph"){
                $graphChannelsJSON = array();
                $graphDataJSON = array();
                foreach($dataJson['channels'] as $key=>$value){
                    $value = str_replace("<br>"," ",$value);
                    array_push($graphChannelsJSON, $value);
                    $graphDataJSON[$value] = array();
                }

                $graphDatesJSON = array();
                foreach($dataJson['data'] as $key=>$data){
                    array_push($graphDatesJSON, $key);
                    foreach($data as $key1=>$val){
                        //if($val != "n.o"){
                            if($val == "" ){
                                array_push($graphDataJSON[$graphChannelsJSON[$key1]] , "");
                            }else if($val == "n.o" ){
                                array_push($graphDataJSON[$graphChannelsJSON[$key1]] , "-");
                            }else{
                                array_push($graphDataJSON[$graphChannelsJSON[$key1]] ,(float)$val);
                            }
                        //}else{
                            //array_pop($graphDatesJSON);
                        //}

                    }
                }
                $finalJSON = new ArrayObject();
                $finalJSON['dates'] = $graphDatesJSON;
                $finalJSON['values'] = $graphDataJSON;
                $processedJson = $finalJSON;
            }
            return $processedJson;
        }

        private function getDataJSON($fromDate, $toDate, $folSeq, $timeInterval, $channelNoArr,$periodType,$exemptions,$stationType){
            if (strpos($timeInterval,'hours') !== false) {
                $timeInterval = "1hour";
            }

            $CCDS = ChannelConfigurationDataStore::getInstance();
            $ChannelsInfo = $CCDS->FindByFolderAndChannelNos($folSeq,$channelNoArr);
            $chArr = new ArrayObject();
            $chNumbersArr = array();
            foreach($ChannelsInfo as $channel){
                $channelUnit = $channel->getChannelUnit();
                $channelName = $channel->getChannelName();

                if(ConvertorUtils::getPrescribedUnit($channelName) != null){
                    $channelUnit = ConvertorUtils::getPrescribedUnit($channelName);
                }
                $channelUnit = mb_check_encoding($channelUnit, 'UTF-8') ? $channelUnit : utf8_encode($channelUnit);
                $chArr[$channelName] = $channelUnit;
                array_push($chNumbersArr,$channel->getChannelNumber());
            }
            $jsonData['channels'] = $chArr;
            $WQDS = WQDDataDataStore::getInstance();
            $WQDStackDS = WQDStackDataStore::getInstance();
            $allDatesData = array();
            $dataArray = null;
            //make special stack call here to the different datastore with same api structure
            $dataArray = null;
            if($stationType == "stack" || $stationType == "effluent"){
               $dataArray = $WQDStackDS->getChannels($fromDate,$toDate,$folSeq,$channelNoArr,$timeInterval);
            }else{
               $dataArray = $WQDS->getChannels($fromDate,$toDate,$folSeq,$channelNoArr,$timeInterval);
            }
            foreach($dataArray as $data){
                $channelData = array();
                $dated = $data['wqdfiledatadated'];

                $chValues = array();
                $chStatuses = array();
                $cnt = (count($data)-2)/4; // divided by 2 becos array produces both channelname and int values
                for($i = 0;$i<$cnt;$i++){
                    $vl = (float)$data["ch".$channelNoArr[$i]."value"];
                    if($vl < 0 || $vl == NULL){
                        $vl = "n.o";
                    }
                    $isDateExempted = false;
                    foreach($exemptions as $exem){
                        $exemption = new Exemption();
                        $exemption = $exem;
                        $isDateExempted = DateUtils::isDateInBetween($exemption->getFromDateRange(),$exemption->getToDateRange(),$dated);
                        if($isDateExempted){
                            $chNo = $channelNoArr[$i];
                            $exemptedChannelsNos = $exemption->getChannelNumbers();
                            if(in_array($chNo, $exemptedChannelsNos)){
                                $vl = StringUtils::$exemptedString;
                            }
                        }
                    }

                    array_push($chValues, $vl);
                    array_push($chStatuses, (float)$data["ch".$channelNoArr[$i]."status"]);
                }
                //$channelData['values'] = $chValues;
                $allDatesData[strtotime($dated)]['channelValue'] = $chValues;
                $allDatesData[strtotime($dated)]['channelStatuses'] = $chStatuses;

                //array_push($allDatesData,$channelData);
            }

            $jsonData['data'] = $allDatesData;
            return $jsonData;
        }

        private function getAverageData($fromDateStr, $toDateStr, $folderSeq){
            $WQDDataDS = WQDDataDataStore::getInstance();
            $WQDAvgInfo = $WQDDataDS->getChannelsAverageInfo($folderSeq,$fromDateStr,$toDateStr);
        }

        private function getZeroSpanValues($data){
            //return $data;
            $currDate = null;
            $lastDate = null;
            $lastValue = null;
            $newArr = array();
            foreach($data as $key=>$value){
                if($lastDate == null){
                    $lastDate = $key;
                    $lastValue = $value;
                    continue;
                }
                $currDate = $key;
                if((round(abs(strtotime($currDate) - strtotime($lastDate)) / 60,2)) != 5){
                    $newArr[$lastDate]= $lastValue;
                }
                $lastValue = $value;
                $lastDate = $key;
            }
            return $newArr;
        }//get last value from the given observations of gap 5 mins
        private function isZeroSpanChannel($chName){
            $channelNames = array("CO","SO2","NO","NO2","NOx","Ozone(O3)");
            if(in_array($chName,$channelNames)>0){
                return true;
            }
            return false;
        }


        private static function getFinalDataByValueType($timeBase,$dateSlices,$folSeqArray,$folChannelDataArray,
                $folChannelsNamesArray,$valueType,$isAverage,$stationType){



            if($isAverage == true){
                $dfinal = self::getAverateDataByTimeInterval($timeBase,$dateSlices,$folSeqArray,$folChannelDataArray,
                    $folChannelsNamesArray,$valueType);
                return $dfinal;
            }
            if(($stationType = "stack" || $stationType = "effluent" )&& $isAvarage != true){
                $dfinal = self::getStackFinalData($timeBase,$dateSlices,$folSeqArray,$folChannelDataArray,$folChannelsNameArray,$valueType,$stationType);
                return $dfinal;
            }
            $dateFormat = "d-m-Y H:i";
            if($stationType == "stack" || $stationType == "effluent"){
                $dateFormat = "d-m-Y H:i:s";
            }
            $dataFinal = array();
            foreach($dateSlices as $dateSlice){
                $dateSliceData = array();
                foreach($folSeqArray as $folSeq){
                    $dataArray = $folChannelDataArray[$folSeq];
                    $dateSliceLong = strtotime($dateSlice);
                    $dateSliceDataArray = $dataArray[$dateSliceLong];
                    $channelNamesArr = $folChannelsNamesArray[$folSeq];
                    if($dateSliceDataArray != null){
                        foreach($dateSliceDataArray['channelValue'] as $key => $eachdata){
                            $eachDataPU = "";
                            if($valueType == "zero" && $dateSliceDataArray['channelStatuses'][$key] == 66){
                                $eachDataPU = ConvertorUtils::getPrescribedValue($channelNamesArr[$key],$eachdata);
                            }else if($valueType == "span" && $dateSliceDataArray['channelStatuses'][$key] == 67){
                                $eachDataPU = ConvertorUtils::getPrescribedValue($channelNamesArr[$key],$eachdata);
                            }else if($valueType == "normal" &&
                                    ($dateSliceDataArray['channelStatuses'][$key] == 128 ||
                                        $dateSliceDataArray['channelStatuses'][$key] == 129)){
                                $eachDataPU = ConvertorUtils::getPrescribedValue($channelNamesArr[$key],$eachdata);
                            }
                            array_push($dateSliceData,$eachDataPU);
                        }
                    }
                }
                $dateSliceDataClean= array_filter($dateSliceData, function($value){
                    return $value != "";
                });
                if(count($dateSliceDataClean) > 0){
                    $dat = new DateTime($dateSlice);
                    $datStr = $dat->format($dateFormat);
                    $dataFinal[$datStr] = $dateSliceData;
                }

            }//dateFinal is date as key and array of channel values
            return $dataFinal;

        }
        private static function getValidatedDataOnly($dataFinal, $validatedDateSlicesByChannel){
            $longKeysDataArr = array();
            foreach($dataFinal as $key=>$value){
                foreach($value as $chKey => $chvalue){
                    $longKeysDataArr[$chKey][strtotime($key)] = $chvalue;
                }

            }

            $longKeysSlicesArr = array();
            $t = 1;
            foreach($validatedDateSlicesByChannel as $key=>$value){
                $longKeysSlicesArr[$t]= array();
                foreach($value as $dated){
                    array_push($longKeysSlicesArr[$t],strtotime($dated)) ;
                }
                $t++;
            }

            $newDataFinal = array();
            foreach($longKeysSlicesArr as $chLongDates){
                foreach($chLongDates as $longDate){
                    $date = new DateTime();
                    $date->setTimestamp($longDate);
                    $datedStr = $date->format("d-m-Y  H:i:s");
                    $newDataFinal[$datedStr] = array();
                }
            }

            $totalChannels= count($longKeysDataArr);
            foreach($newDataFinal as $key=>$emptyarr){
                $longDate = strtotime($key);
                for($i = 0 ; $i<$totalChannels; $i++){
                    if(in_array($longDate,$longKeysSlicesArr[$i+1])){
                        $chValue = $longKeysDataArr[$i][$longDate];
                    }else{
                        $chValue = "n.v";
                    }
                    $newDataFinal[$key][$i] = $chValue;
                }
            }
            ksort($newDataFinal);
            return $newDataFinal;
        }

        private static function getAverateDataByTimeInterval($timeBase,$dateSlices,$folSeqArray,$folChannelDataArray,$folChannelsNamesArray,$valueType){
            $dataFinal = array();
            $WQDDataDS = WQDDataDataStore::getInstance();

            foreach($folSeqArray as $folSeq){
                $dataArray = $folChannelDataArray[$folSeq];
                $dataFinal = $WQDDataDS::getAverageDataByDataArrayDataSlices($timeBase,$dataArray,$dateSlices,$folChannelsNamesArray[$folSeq],$valueType,$dataFinal);

            }
            return $dataFinal;
        }

        private static function getStackFinalData($timeBase,$dateSlices,$folSeqArray,$folChannelDataArray,$folChannelsNameArray,$valueType,$stationType){
            $dateFormat = "d-m-Y H:i";
            if($stationType == "stack" || $stationType == "effluent"){
                $dateFormat = "d-m-Y H:i:s";
            }
            $dataFinal = array();
            foreach($dateSlices as $dateSlice){
                $dateSliceData = array();
                foreach($folSeqArray as $folSeq){
                    $dataArray = $folChannelDataArray[$folSeq];
                    $dateSliceDataArray = null;
                    $dateSliceDataArray = self::getNextStackData($dataArray,strtotime($dateSlice));

                    $dateSliceLong = strtotime($dateSlice);
                    if($dateSliceDataArray != null){
                        foreach($dateSliceDataArray['channelValue'] as $key => $eachdata){
                            array_push($dateSliceData,$eachdata);
                        }
                    }
                }
                $dateSliceDataClean= array_filter($dateSliceData, function($value){
                    return $value != "";
                });
                if(count($dateSliceDataClean) > 0){
                    $dat = new DateTime($dateSlice);
                    $datStr = $dat->format($dateFormat);
                    $dataFinal[$datStr] = $dateSliceData;
                }

            }//dateFinal is date as key and array of channel values
            return $dataFinal;
        }
        private static function getNextStackData($dataArray,$dateSliceLong){
            $dateSliceDataArray = $dataArray[$dateSliceLong];
            if($dateSliceDataArray == null){
                foreach($dataArray as $key => $data){
                    if($key >= $dateSliceLong){
                        $dataHour = date('H', $key);
                        $sliceHour = date('H', $dateSliceLong);
                        if($dataHour == $sliceHour){
                        return $dataArray[$key];
                        }
                    }
                }
            }
            return $dateSliceDataArray;
        }

        //Getting averages, min, max values for the grid starts here//
        private static function getAvgMinMaxValues($finalDataJson){
            $finalJSON = $finalDataJson;
            $avgArr = array();

            $avgArr['avg'] =  array();
            $avgArr['min'] =  array();
            $avgArr['max'] =  array();

            for($i = 0 ; $i < count($finalJSON['channels']); $i++){
				$avgArr['avg'][$i] = "n.o";
				$avgArr['min'][$i] = "n.o";
				$avgArr['max'][$i] = "n.o";
				
                $allVals = array();
                foreach($finalDataJson['data'] as $data){
                    if($data[$i] > 0){
                        array_push($allVals, $data[$i]);
                    }
                }
                if(count($allVals) > 0){
					$avgValue = array_sum ( $allVals ) / count($allVals);
					$arr = array_diff($allVals, array('n.o'));
					$avgArr['avg'][$i] = round($avgValue,2);
					if(count($arr) > 0){
						$avgArr['min'][$i] = min($arr);
						$avgArr['max'][$i] = max($arr);
					}
					
					
				}
            }

            $finalJSON['avgminmax'] = $avgArr;
            return $finalJSON;
        }
        //Getting averages, min, max values for the grid ends here//
  }
?>