<?php
  
  require_once('../IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects//Folder.php");
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects//ChannelConfiguration.php");
  
  class DropDownUtils{
      private static $stationType = array("aqms","stack","effluent");
      public static function getAllLocationsDropDown($selectName,$onChangeMethod,$selectedValue,$noSelectionValue = null){
        $LDS = LocationDataStore ::getInstance();
        $locations = $LDS->FindAll();
        $str = "<select name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value='0'>Select Location</option>";
        
        if(!empty($noSelectionValue)){
            $str .= "<option value='0'>$noSelectionValue</option>";        
        }else{
            $str .= "<option value='0'>Select Location</option>";
        }        
        if($locations != null && $locations <> "" ){
            foreach($locations as $location){
                $select = $selectedValue == $location->getSeq() ? 'selected' : null;  
                $str .= "<option value='" . $location->getSeq() . "'" . $select . ">" . $location->getLocationName() . "</option>";                               
            } 
        }
        $str .= "</select>";
        return $str;
    }
    public static function getUserLocationsDropDown($seq,$selectName,$onChangeMethod,$selectedValue,$noSelectionValue = null){
        $LDS = LocationDataStore ::getInstance();
        $locations = $LDS->FindLocationArrByUser($seq);
        $str = "<select required='' class='form-control m-b' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";        
        if(!empty($noSelectionValue)){
            $str .= "<option value=''>$noSelectionValue</option>";        
        }else{
            $str .= "<option value=''>Select Location</option>";
        }        
        if($locations != null && $locations <> "" ){
            foreach($locations as $location){
                $select = $selectedValue == $location->getSeq() ? 'selected' : null;  
                $str .= "<option value='" . $location->getSeq() . "'" . $select . ">" . $location->getLocationName() . "</option>";                               
            } 
        }
        $str .= "</select>";
        return $str;
    }
     public static function getAllLocationsMultiDropDown($selectName,$onChangeMethod,$selectedValuel){
        $LDS = LocationDataStore ::getInstance();
        $locations = $LDS->FindAll();
        $str = "<select multiple class='chosen-select' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value='0'>Select Location</option>";
        if($locations != null && $locations <> "" ){
            foreach($locations as $location){
                $select = $selectedValue == $location->getSeq() ? 'selected' : null;  
                $str .= "<option value='" . $location->getSeq() . "'" . $select . ">" . $location->getLocationName() . "</option>";                               
            } 
        }
        $str .= "</select>";
        return $str;
    }
    public static function getFoldersDropDownWithStationName($folders,$selectName,$onChangeMethod,$selectedValue){
        $str = "<select required class='form-control m-b' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value=''>Select a folder</option>";
        if($folders != null && $folders <> "" ){
            foreach($folders as $folder){
                $folderObj = new Folder();
                $folderObj = $folder;
                $name = $folder->getStationName();
                if(empty($name)){
                    $name =  $folderObj->getFolderName();   
                }
                $select = $selectedValue == $folderObj->getSeq() ? 'selected' : null;  
                $str .= "<option value='" . $folderObj->getSeq() . "'" . $select . ">" . $name . "</option>";                               
            } 
        }
        $str .= "</select>";
        return $str;
    }
    public static function getChannelsDropDown($channels,$selectName,$onChangeMethod,$selectedValue){
        $str = "<select required class='form-control m-b' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value='0'>Select a ChanelConfig</option>";
        if($channels != null && $channels <> "" ){
            foreach($channels as $channel){
                $channelObj = new ChannelConfiguration();
                $channelObj = $channel;
                $select = $selectedValue == $channelObj->getChannelNumber() ? 'selected' : null;
                $chNo = $channelObj->getChannelNumber();
                $chName = $channelObj->getChannelName();
                $pLimit = $channelObj->getPrescribedLimit();
                $channelInfo = $chName ." ". $channelObj->getChannelStation();
                $channelInfo .= empty($pLimit) ? "" : " (Pres. ".$pLimit.")";
                $str .= "<option value='" . $chNo . "'" . $select . ">" . $channelInfo . "</option>";                               
            } 
        }
        $str .= "</select>";
        return $str;
    } 
    
    public static function getStationTypeDropDown($folders,$selectName,$onChangeMethod,$selectedValue){
        $str = "<select name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value='0'>Select Station Type</option>";
        $select = $selectedValue == $folderObj->getSeq() ? 'selected' : null;  
        $str .= "<option value='" . $folderObj->getSeq() . "'" . $select . ">" . $folderObj->getFolderName() . "</option>";                               
        $str .= "</select>";
        return $str;
    }
    
    public static function getFolderTypeDropDown($selectName,$selectedValue,$onChangeMethod = null){
        $str = "<select name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $aqms = $selectedValue == "aqms" ? "selected" : null;
        $stack = $selectedValue == "stack" ? "selected" : null;
        $effluent = $selectedValue == "effluent" ? "selected" : null;
        $str .= "<option value='aqms' $aqms>AQMS</option>";
        $str .= "<option value='stack' $stack>CEMS</option>";
        $str .= "<option value='effluent' $effluent>EFFLUENT</option>";        
        $str .= "</select>";
        return $str;
    }   
    
    public static function getFoldersDropDown($folders,$selectName,$onChangeMethod,$selectedValue){
        $str = "<select name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value='0'>Select a Station</option>";
        if($folders != null && $folders <> "" ){
            foreach($folders as $folder){
                $folderObj = new Folder();
                $folderObj = $folder;
                $select = $selectedValue == $folderObj->getSeq() ? 'selected' : null;  
                $str .= "<option value='" . $folderObj->getSeq() . "'" . $select . ">" . $folderObj->getFolderName() . "</option>";                               
            } 
        }
        $str .= "</select>";
        return $str;
    }
 
  }
?>
