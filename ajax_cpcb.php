<?php
      require_once('IConstants.inc');
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/CPCBMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/StationReportMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/MultiStationReportMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/EffluentCumulativeFlowReportMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/WindRoseReportMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/CommentsMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/ExemptionMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/DateUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/ExportUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/PrescribedLimitsUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/StringUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/CommentsDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ExemptionDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/UserDataStore.php");

      $REQUEST_ALL_LOCATIONS = "requestAllLocationsAndStationsJSON";
      $REQUEST_STATIONS_BY_LOCATION = "requestStationsByLocationJSON";
      $REQUEST_CHANNELS_BY_FOLDER = "requestChannelsByFolderJSON";
      $REQUEST_STATION_REPORT = "requestStationReport";
      $REQUEST_STATION_CHART_JSON = "requestStationChartJSON";
      $REQUEST_WIND_ROSE_JSON = "requestWindRoseReport";
      $REQUEST_MAP_LOCATIONS_JSON = "requestMapLocationsJSON";
      $REQUEST_STATIONS_RECENT = "requestRecent";
      $SAVE_COMMENTS_MASTER = "saveCommentsMaster";
      $SAVE_COMMENTS_DETAILS = "saveCommentsDetails";
      $REQUEST_COMMENTS_MASTER = "requestCommentsMaster";
      $DELETE_COMMENTS_MASTER = "deleteCommentsMaster";
      $REQUEST_COMMENTS_DETAILS = "requestCommentsDetail";
      $DELETE_COMMENTS_DETAILS = "deleteCommentsDetails";
      $SAVE_EXEMPTION ="saveExemption";
      $REQUEST_EXEMPTIONS = "requestExemptions";
      $DELETE_EXEMPTION = "deleteExemption";
      $APPROVE_EXEMPTION = "approveExemption";
      $REQUEST_EXEMPTION_COMMENTS ="requestExemptionComments";
      $SAVE_EXEMPTION_COMMENT = "saveExemptionComment";
      //New API from industry interface with common request to validate/exempt/record
      $SAVE_VALIDATION_OR_EXEMPTION = "saveValidationExemptionRequest";

      $method = $_GET['method'];
      //$locSeqsArr = CPCBMgr::getCPCBLocSeqsArray();
      $json = null;
      if($method == $REQUEST_ALL_LOCATIONS){
            session_start();
            $userLogged = new User();
            $userLogged = $_SESSION["userlogged"];
            $userSeq = $userLogged->getSeq();
            $userLocationSeq = $userLogged->getLocationSeq();
            $LDS = LocationDataStore::getInstance();
            $locationsArr = $LDS->FindLocationsByUser($userSeq);
			if($userLocationSeq){
                array_push($locationsArr,$userLocationSeq);
            }
            $locations = null;
            $locationsSeqsStr = implode(",",$locationsArr);
            $folderType = $_GET['folderType'];
            $locations = $LDS->FindBySeqs($locationsSeqsStr);
            $locArr = array();
            $locArr['0'] = "All";
            foreach($locations as $location){
                $locArr[$location->getSeq()] = $location->getLocationDetails();
            }
            $FDS = FolderDataStore::getInstance();
            $folders = $FDS->FindByLocationSeqs($locationsSeqsStr);
            $locArr1 = array();
            $locArr1['0'] = "All";
            $folArr = array();
            $locFolArr = array();
            foreach($folders as $folder){
                if($folder->getStationType() == strtolower($folderType) || $folderType == "all"){
                        $locName = $locArr[$folder->getLocationSeq()];
                        $locArr1[$folder->getLocationSeq()] = $locName;
                        if($folder->getIsVisible() == 1){
                            $loc = $locations[$folder->getLocationSeq()];                            
                            $fol  = array("folderName"=>$folder->getStationName(),"folderLocation"=>$loc->getLocationName());
                            $folArr[$folder->getSeq()] = $fol;    
                        }
                    
                }
            }
            $json = new ArrayObject();
            $json['locations'] = $locArr1;
            $json['folders'] = $folArr;
            echo json_encode($json);
      }
      if($method == $REQUEST_STATIONS_BY_LOCATION){
            $locSeq = $_GET['locSeq'];
            $stationType = $_GET['stationType'];
            $FDS = FolderDataStore::getInstance();
            $folders = $FDS->FindByLocation($locSeq);
            $folArr = new ArrayObject();
            foreach($folders as $folder){
                if($folder->getStationType() == strtolower($stationType) || $stationType == "ALL"){
                    if($folder->getIsVisible() == 1){
                        $fol  = array("folderName"=>$folder->getStationName(),"folderLocation"=>$folder->getLocation());
                        $folArr[$folder->getSeq()] = $fol;
                    }
                }
            }
            echo json_encode($folArr);
      }

      if($method == $REQUEST_CHANNELS_BY_FOLDER){
          $folSeq = $_GET['folSeq'];   
          $FDS = FolderDataStore::getInstance();   
          $folder = $FDS->FindBySeq($folSeq);
          $info = $folder->getIndustryName();
          $info .=  empty($info) ? "" : ", " . $folder->getCity();
          $info .=  empty($info) ? "" : ",". $folder->getState().". ";
          $category =  $folder->getCategory();
          $info .=  empty($category) ? "" : "<strong>Category</strong> : $category";                 
          $CCDS = ChannelConfigurationDataStore::getInstance();
          $channelsInfo = $CCDS->FindByFolder($folSeq);
          $chArr = new ArrayObject();
          foreach($channelsInfo as $channel){
				$chName = $channel->getChannelName();
				if($channel->getChannelStation() != null){
					$chName .= " -". $channel->getChannelStation();
				}
				$chArr[$channel->getChannelNumber()] = $chName;
            }
			if($folSeq == "48"){
			  $comments = "<p><a target='new' href='http://123.63.167.133:8011'>Live Video For MCL</a></p><H3>Use the above link to view live video feed. <br>You can use username as MCL_KOTA and password as MCLKOTA</h3><p>Note: open this link on Internet Explorer and install plug-in first time.</p>";
			  $chArr['video'] = $comments;
		  }
            $data["channels"] =  $chArr;
            $data["folderInfo"] = $info;
            echo json_encode($data);
      }

      if($method == $REQUEST_STATION_REPORT){
        $rep = null;
        if($_GET['isMultiStation'] != null){
            if($_GET['iscumulative']  != null){
                $stationRepMgr = EffluentCumulativeFlowReportMgr::getInstance();
                $rep = $stationRepMgr->getCumulativeReport($_GET);
            }else{
                $stationRepMgr = MultiStationReportMgr::getInstance();
                $rep = $stationRepMgr->getMultiStationReport($_GET);
            }
        }else{
            $stationRepMgr = StationReportMgr::getInstance();
            $rep = $stationRepMgr->getStationReport($_GET);
        }
        echo json_encode($rep);
      }

      if($method == $REQUEST_WIND_ROSE_JSON){
         $windRoseReportMgr = WindRoseReportMgr::getInstance();
         $windRosePlot = $windRoseReportMgr->getWindRoseReport($_GET);
         echo json_encode($windRosePlot);
      }

      if($method == $REQUEST_MAP_LOCATIONS_JSON){
         session_start();
         $userLogged = new User();
         $userLogged = $_SESSION["userlogged"];
         $isLoggedIn = $_GET['isLoggedIn'];
         $cpcbMgr = CPCBMgr::getInstance();
         $UDS = UserDataStore::getInstance();
         $folderSeqs = $UDS->getAllFolderSeqs($userLogged->getSeq());
         $mapJSON = $cpcbMgr->getCPCBMapsJson($isLoggedIn,$folderSeqs);
         echo json_encode($mapJSON);
      }

      if($method == $REQUEST_STATIONS_RECENT){
         $stationRepMgr = MultiStationReportMgr::getInstance();
         $recentJSON = $stationRepMgr->getRecentReport($_GET);
         echo json_encode($recentJSON);
      }

      if($method == $SAVE_COMMENTS_MASTER){
         $commentsMgr = CommentsMgr::getInstance();
         $res = $commentsMgr->saveCommentsMaster($_GET);
         echo json_encode($res);
      }

      if($method == $REQUEST_COMMENTS_MASTER){
         $locationSeq = $_GET['lsp'];
         session_start();
         $userLogged = new User();
         $userLogged = $_SESSION["userlogged"];
         $userSeq = $userLogged->getSeq();

         $LDS = LocationDataStore::getInstance();
         $locationsArr = $LDS->FindLocationsByUser($userSeq);
         if(!in_array($userLogged->getLocationSeq(),$locationsArr)){
            array_push($locationsArr, $userLogged->getLocationSeq());    
         }
         if(count($locationsArr) == 0){
            echo json_encode("");
            return;
         }
         $locations = null;
         $locationsSeqsStr = implode(",",$locationsArr);
         $commentsMgr = CommentsMgr::getInstance();
         $comments = $commentsMgr->findAllCommentsMasterJSON($locationsSeqsStr);
         echo $comments;
      }
      if($method == $DELETE_COMMENTS_MASTER){
         $commentsMgr = CommentsMgr::getInstance();
         $bool = $commentsMgr->deleteCommentsMasterBySeq( $_GET['seq']);
         $res = "SUCCESS";
         if($bool == false){
            $res = "FAILURE";
         }
         echo json_encode(array("RESPONSE"=>$res ));
      }

      if($method == $SAVE_COMMENTS_DETAILS){
         $commentsMgr = CommentsMgr::getInstance();
         session_start();
         $userLogged = new User();
         $userLogged = $_SESSION["userlogged"];
         $userSeq = $userLogged->getSeq();
         $res = $commentsMgr->saveCommentsDetail($_GET,$userSeq);
         echo json_encode($res);
      }

      if($method == $REQUEST_COMMENTS_DETAILS){
         $commentsMgr = CommentsMgr::getInstance();
         $comments = $commentsMgr->findAllCommentsDetailJSON($_GET['seq']);
         echo $comments;
      }
      if($method == $DELETE_COMMENTS_DETAILS){
         $commentsMgr = CommentsMgr::getInstance();
         $bool = $commentsMgr->deleteCommentsDetailsBySeq($_GET['seq']);
         $res = "SUCCESS";
         if($bool == false){
            $res = "FAILURE";
         }
         echo json_encode(array("RESPONSE"=>$res ));
      }
      if($method == $SAVE_EXEMPTION){
         $exmpMgr = ExemptionMgr::getInstance();
         $bool = $exmpMgr->saveExemptionMaster($_GET);
         $res = "SUCCESS";
         if($bool == false){
            $res = "FAILURE";
         }
         echo json_encode(array("RESPONSE"=>$res ));
      }
      if($method == $REQUEST_EXEMPTIONS){
         $exemptionMgr = ExemptionMgr::getInstance();
         session_start();
         $userLogged = new User();
         $userLogged = $_SESSION["userlogged"];
         $isExemption = $_GET["isExemption"];
         if($userLogged->getUserName() == "cpcb"){
            $exmps = $exemptionMgr->findAllExemptionJSON($isExemption);
         }else{
            $exmps = $exemptionMgr->findByLocationSeqJSON($userLogged->getLocationSeq(),$isExemption);
         }
         echo $exmps;
      }
      if($method == $DELETE_EXEMPTION){
         $exemptionMgr = ExemptionMgr::getInstance();
         $bool = $exemptionMgr->deleteExemptionBySeq( $_GET['seq']);
         $res = "SUCCESS";
         if($bool == false){
            $res = "FAILURE";
         }
         echo json_encode(array("RESPONSE"=>$res ));
      }
      if($method == $APPROVE_EXEMPTION){
        $exemptionMgr = ExemptionMgr::getInstance();
        $bool = $exemptionMgr->approveExemption( $_GET['seq'],$_GET['flag']);
        $res = "SUCCESS";
         if($bool == false){
            $res = "FAILURE";
         }
         echo json_encode(array("RESPONSE"=>$res ));
      }
      if($method == $REQUEST_EXEMPTION_COMMENTS){
         $exemptionMgr = ExemptionMgr::getInstance();
         $comments = $exemptionMgr->findCommentsByExemptionSeqJSON($_GET['seq']);
         echo $comments;
      }
      if($method == $SAVE_EXEMPTION_COMMENT){
         $exemptionMgr = ExemptionMgr::getInstance();
         $res = $exemptionMgr->saveExemptionComment($_GET);
         echo json_encode($res);
      }

      if($method == $SAVE_VALIDATION_OR_EXEMPTION){
          $commentsMgr = CommentsMgr::getInstance();
          $res = $commentsMgr->saveValidationOrExeptionMaster($_GET);
          echo json_encode($res);
      }
?>

