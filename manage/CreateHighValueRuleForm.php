 <?php
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//HighValueRuleDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
    
    
   
    
    
   $highValueRule = new HighValueRule();
   $HVRDS = HighValueRuleDataStore::getInstance();
   $FDS = FolderDataStore::getInstance();    
   if($_POST["editSeq"] <> "" ){
        $highValueRule = $HVRDS->FindBySeq($_POST["editSeq"]);   
   }  
   
  if($_POST["submit"]<>""){
      $folderSeq = $_POST["folder"];
      $folder = $FDS->FindBySeq($folderSeq);
      $emailIds = $_POST["emailIds"];      
      $mobileNos = $_POST["mobileNos"];
      $parameter = $_POST["channelNames"];
      $highValue = $_POST["highValue"]; 
      $frequency = $_POST["frequency"];
      $lastRuleHitFileDataSeq = $_POST["lastRuleHitFileDataSeq"];
      
      $active = $_POST["active"];
      $seq = $_POST["seq"];
      
      
      $highValueRule->setSeq($seq);
      $highValueRule->setFolderSeq($folderSeq);
      $highValueRule->setEmail($emailIds);
      $highValueRule->setMobile($mobileNos);
      $highValueRule->setParameter($parameter);
      $highValueRule->setHighValue($highValue);
      $highValueRule->setFrequency($frequency);
      $highValueRule->setStationType($folder->getStationType());
      $highValueRule->setIsActive($active);
      $highValueRule->setLastRuleHitFileDataSeq($lastRuleHitFileDataSeq);
      $highValueRule->setRuleHits(0);
     //------------------------validations--------------------------------- 
    
    $messageText = "";    
    $div = "";
    if($folderSeq == 0){
        $messageText .= "- Station is Required<br>";    
    }
    $messageText .=  validator::validateform("Parameter",$parameter,256,false); 
    $messageText .=  validator::validateform("Highest Value",$highValue,256,false); 
    $messageText .=  validator::validateform("Frequency",$frequency,256,false); 
       
     //------------------------------************----------------------------------
     
                                                                         
     
    //------------------------Show Validation or save object---------------------------------   
    if($messageText != null && $messageText != ""){
      $div = "         <div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error during save user details :</strong> <br/>" . $messageText . "</p>
                       </div></div>" ; 
    }else{ 
        $HVRDS->Save($highValueRule);
        $messageText = "High Value Rule Saved Successfully";
        $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Message:</strong>&nbsp;" . $messageText . "</p> 
                       </div></div>";
                       
        $highValueRule = new HighValueRule();
    }
    header('Location: showHighValueRules.php');
    //------------------------------************----------------------------------   
  }

?>



<!DOCTYPE html>
<html>
    <head>
    <? include("_jsAdminInclude.php");?>
    <?include("../_InspiniaInclude.php");?>
    </head>
    <body>
        <Div class="wrapper">    
            <? include("leftButtons.php");
                  $locSeq = $managerSession['locSeq'];
                  $FDS = FolderDataStore::getInstance();
                  $CCDS = ChannelConfigurationDataStore::getInstance();
                  $folders = $FDS->FindByLocation($locSeq);
                  $folDDown = DropDownUtils::getFoldersDropDownWithStationName($folders,"folder","changeStation()",$highValueRule->getFolderSeq());
                  $chDDown = "Select a Station to load Parameters";
                  if($highValueRule->getParameter() != null){
                    $channelConfigs = $CCDS->FindByFolder($highValueRule->getFolderSeq());
                    $chDDown = DropDownUtils::getChannelsDropDown($channelConfigs,"channelNames","",$highValueRule->getParameter());
                  }     
            ?>
             <Div id="page-wrapper" class="gray-bg"> 
                <table width="80%" border="0">
                   <tr>       
                    <td style="padding:10px 10px 10px 10px;"><?php echo($div) ?></td>
                   </tr> 
                  <tr>
                    <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Enter New High Value Rule Details </td>
                    </tr>
                  <tr>
                    <td class="ui-widget-content">        
                        <form name="frm1" method="post" action="createHighValueRuleForm.php">
                            <input type="hidden" name="seq" id="seq" value="<?php echo ($highValueRule->getSeq());?>" / >
                            <input type="hidden" name="lastRuleHitFileDataSeq" id="seq" value="<?php echo ($highValueRule->getLastRuleHitFileDataSeq());?>" / >
                             <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                             <tr>
                                <td width="22%">Station:</td>
                                <td width="78%">
                                    <b><? echo $folDDown; ?></b>
                                    
                                </td>
                              </tr>
                             <tr>
                                <td width="22%">Email id(s) :</td>
                                <td width="78%"><input name="emailIds" type="text" value="<?php echo($highValueRule->getEmail());?>"  size="50"></td>
                              </tr>
                              <tr>
                                <td width="22%">Mobile No(s) :</td>
                                <td width="78%"><input name="mobileNos" type="text" value="<?php echo($highValueRule->getMobile());?>"  size="50"></td>
                              </tr>
                              <tr>
                                <td>Parameter :</td>
                                <td class="parameterTD"><? echo $chDDown; ?></td>
                              </tr>
                               <tr>
                                <td>Highest Value :</td>
                                <td><input name="highValue" type="text" value="<?php echo($highValueRule->getHighValue());?>" size="50"></td>
                              </tr>
                              <tr>
                                <td>Frequency :</td>
                                <td><input name="frequency" type="text" value="<?php echo($highValueRule->getFrequency());?>" size="50"></td>
                              </tr>
                               <tr>
                                <td>Activate :</td>
                                 <?php
                                       if($highValueRule->getIsActive() == "" || $highValueRule->getIsActive() == "1"){
                                         $checked_On = "checked";
                                       }else{
                                          $checked_Off = "checked"; 
                                       }
                                            
                                     ?> 
                                <td><input name="active" value="true" type="radio"  <?php echo ($checked_On); ?> >On
                                     <input name="active" value="false" type="radio"  <?php echo ($checked_Off); ?> >Off</td>
                              </tr>
                              <tr>
                                <td>&nbsp;</td>
                                <td>
                                    <input type="submit" name="submit" value="Save" checked> 
                                    <input type="reset" name="Reset" value="Reset">
                                </td>
                              </tr>
                            </table>
                          </form> 
                     </td>
                    </tr>
                    
                </table>
            </Div>
        </Div>
    </body>
</html>