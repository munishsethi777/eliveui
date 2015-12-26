<?
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require($ConstantsArray['dbServerUrl'] . "Utils//FileSystemUtils.php");      
require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//M2MSynchronizerDataStore.php");

$LDS = LocationDataStore ::getInstance();
$locations = $LDS->FindAll();
$locationSeq = $_POST["locationSeq"];
$FDS = FolderDataStore::getInstance();
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
            $seq = $managerSession['seq'] ;
            if(!empty($locationSeq)){
                  $folders = $FDS->FindByLocation($locationSeq);    
            }else{
                   $seq = $managerSession['seq'];
                   $locationSeqs = $LDS->FindLocationsByUser($seq);
                   $lseq = $managerSession['locSeq'];
                   if(!in_array($lseq,$locationSeqs)){
                        array_push($locationSeqs,$lseq);    
                   }
                   $folders = $FDS->FindByLocationSeqs(implode(",",$locationSeqs)); 
            }  
         ?>
        <div id="page-wrapper" class="gray-bg">     
            <table width="80%" border="0">
              <tr>
             
                <td>
                    Select Location :
                    <? echo DropDownUtils::getUserLocationsDropDown($seq,"l_DropDown","populateRows(this.value)",$locationSeq) ?>
                 </td> 
                </tr>
              <tr>
                <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">List of Available Location Folders</td>
                </tr>
              <tr>
                <td class="ui-widget-content">
                  <form name="folderForm" method="post" action="" > 
                    <input type="hidden" name="editSeq" id="editSeq" />
                       <input type="hidden" name="formAction" id="formAction" />
                       <input type="hidden" name="isEnabled" id="isEnabled" />
                       <input type="hidden" name="path" id="path" />  
                        <input type="hidden" name="locationSeq" id="locationSeq" value="<?echo $locationSeq?>" /> 
				        <table width="100%" border="1" bordercolor="silver" style="border-style:dashed;border-width:thin;border:thin;border-color:#CCCCCC">
				          <tr>
                            <td width="2%" class="ui-widget-header">Status</td>
                             <td width="5%" class="ui-widget-header">Visible</td>                   
					        <td width="12%" class="ui-widget-header">Folder</td> 
                            <td width="10%" class="ui-widget-header">Last Synched</td>
                            <td width="10%" class="ui-widget-header">Last Reminder</td>
				          </tr>
				          <? foreach($folders as $folder){
                              $synchedOnStr = "";
                              $parsedOnStr = "";
                              $remindedOnStr = "";
                              
                              if($folder->getLastSynchedOn()!= ""){
                                    $lastSynchedOn = new DateTime($folder->getLastSynchedOn(), new DateTimeZone('Asia/Kolkata'));
                                    $synchedOnStr = $lastSynchedOn->format('Y-m-d H:i:s');
                              }
                              if($folder->getLastParsedOn()!= ""){
                                    $lastParsedOn = new DateTime($folder->getLastParsedOn(), new DateTimeZone('Asia/Kolkata'));
                                    $parsedOnStr = $lastParsedOn->format('Y-m-d H:i:s');
                              }
                              if($folder->getLastSynchedOn()!= ""){
                                    $lastRemindedOn = new DateTime($folder->getLastRemindedOn(), new DateTimeZone('Asia/Kolkata'));
                                    $remindedOnStr = $lastRemindedOn->format('Y-m-d H:i:s');
                              }
                              
                              
                              $loationObj = new Location();
                              $loationObj = $locations[$folder->getLocationSeq()];
                              $locationName = $loationObj->getLocationFolder();
                              $fullPath = $ConstantsArray['rootURL']. "Repository/" . $locationName . "/" . $folder->getActualName();                      
                              $path = $ConstantsArray['rootURL']. "Repository/"   . $locationName . "/" . $folder->getFolderName() ;    
                              $title = "Assign Permissions on Folder (" . $folder->getFolderName() . ") location of (" . $locationName . ")";
                              $statusCol = "<tr><td><img src='images/tick.png' border='0' title='Enabled'/></td>";
                              $visibleCol = "<td><img src='images/tick.png' border='0' title='Visible'/></td>"; 
                              if( $folder->getIsEnable() == "0"){
                                   $statusCol = "<tr><td><img src='images/tick_grey.png' border='0' title='Disabled'/></td>";
                              }
                              if($folder->getIsVisible() == "0"){
                                  $visibleCol = "<td><img src='images/tick_grey.png' border='0' title='InVisible'/></td>";
                              }  
                              $pathCol = "<a target='_blank' href='" . $fullPath . "'>" . $folder->getFolderName() ."</a>";
                              $m2mSiteCode = $folder->getM2MCode();
                              if(!empty($m2mSiteCode)){
                                  $pathCol = $folder->getFolderName();   
                              }
						          echo $statusCol .$visibleCol.
                                    "<td>" . $pathCol ."</td> 
                                    <td>". $synchedOnStr ."</td>
                                    <td>". $remindedOnStr ."</td>
                                        
	         
						          </tr>";
				  	        }
				          ?>
				        </table>
                       </form>
                 </td>
                </tr>
            </table>
        </div>
    </Div>
    <DIV id="showUsersDialog">
        <div id ="showUsersDialogData">
        <div id="showFolders_loadingAjax" style="display: none;"/>
           <img src='images/ajax.gif' />  
        </div>
        <Div class="msg"></div>

        <Div class="UserDivs"></div>
            <label id="folderSeqSelected" style="display:none"></label> 
        </div>
    </DIV>
    </body>
</html>
 <script language="javascript">
        var showFolders_FoldersJSON = null;
        var showFolders_UsersJSON = null;
        var showFolders_UserDivId = 0;
        $(function(){
            buildShowUsersDialog(true);
            buildUsersJson();
            //buildUsersFoldersJsons();
        });  
        function changeStatus(seq,isEnabled){
            document.folderForm.action = "showFolders.php";                   
            document.getElementById('editSeq').value =  seq ;
            document.getElementById('isEnabled').value =  isEnabled ;
            document.getElementById('formAction').value =  'changeStatus' ; 
            document.folderForm.submit();
        } 
        function Edit(seq,locationSeq){
            document.folderForm.action = "createFolder.php"; 
            document.getElementById('locationSeq').value =  locationSeq                 
            document.getElementById('editSeq').value =  seq ;
            document.folderForm.submit();
        }
        function Delete(seq,path){ 
            var r=confirm("Do you really want to delete this folder.");
            if(r == true){ 
                document.folderForm.action = "showFolders.php";                   
                document.getElementById('editSeq').value =  seq ;
                document.getElementById('path').value =  path ;
                document.getElementById('formAction').value =  'delete' ; 
                document.folderForm.submit();
            }
        }
        function populateRows(locationSeq){
            document.getElementById('locationSeq').value =  locationSeq;      
            document.folderForm.action = "showFolders.php";
            document.getElementById('formAction').value =  'populateRows' ; 
            document.folderForm.submit();
        }
 </script>

