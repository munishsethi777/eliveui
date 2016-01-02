<?
$msg="";
$emailMsg="";
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "admin//configuration.php");
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/DropDownUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/Folder.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/ChannelConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/WQDFileDataStore.php");

//$configuration = new Configuration();
Session_start();
$managerSession = $_SESSION["managerSession"];
$userDataStore = UserDataStore::getInstance();
$userSeq =  $managerSession['seq'];
$locSeq = $managerSession['locSeq'];
$FDS = FolderDataStore::getInstance();
$CDS = ChannelConfigurationDataStore::getInstance();
$LDS = LocationDataStore::getInstance();
$WSDS = WQDStackDataStore::getInstance();
$WFDS = WQDFileDataStore::getInstance();
$locationSeqs = $LDS->FindLocationsByUser($userSeq);
if(!in_array($locSeq,$locationSeqs)){
    array_push($locationSeqs,$locSeq);    
}
$folders = $FDS->FindByLocation(implode(",",$locationSeqs));
$isdataExist = false;
$folder = new Folder();
$channlConfigs = array();
$selSeq = 0;
$errMsg = "";
$msg = "";
$disabledChNo = "disabled";
if($_POST["call"] == "delete"){
    $seq = $_POST["delSeq"];
    $CDS->DeleteBySeq(intval($seq));
}
if($_POST["call"] == "edit")
{
     $slectedFolder = $_POST["F_DropDown"];
     if(!empty($slectedFolder)){
        $selSeq = intval($slectedFolder);
         $channlConfigs = $CDS->FindByFolder($selSeq);
         $folder = $folders[$selSeq];
         if($folder->getStationType() == "stack" || $folder->getStationType() == "effluent"){
             $isdataExist = $WSDS->isStackDataExist($selSeq);
         }else{
             $isdataExist = $WFDS->isFileDataExist($selSeq);
         }
     }

}
$dupNoChArr = array();
$chNoArr = array();
if($_POST["action"] == "update")
{
      $channalSeqs = $_POST["chseq"];
      $selSeq = intval($_POST["selectedFolderSeq"]);
      if($selSeq == 0){
          $errMsg = "Please Select folder and click on Edit.";
      }else{
         $folder = $folders[$selSeq];
         if($folder->getStationType() == "stack" || $folder->getStationType() == "effluent"){
             $isdataExist = $WSDS->isStackDataExist($selSeq);
         }else{
             $isdataExist = $WFDS->isFileDataExist($selSeq);
         }
          $channlConfigs = $CDS->FindByFolder($selSeq);
          $chSeqs = $_POST["chseq"];
          $count = 1;
          foreach($chSeqs as $chseq){
            if(!empty($chseq)){
                $chConfigObj = $channlConfigs[intval($chseq)];
            }else{
                 $chConfigObj = new ChannelConfiguration();
                 $chConfigObj->setFolderSeq($selSeq);
                 $chConfigObj->setChannelStatusFlag(1);
            }
            if(!$chConfigObj){
                continue;
            }
            $chNo = $_POST["chno".$count];
            $chName = $_POST["chName".$count];
            $chUnit = $_POST["chUnit".$count];
            $chSubStation = $_POST["substation".$count];
            $prescribedLimit = $_POST["prescribedlimit".$count];
            $chConfigObj->setChannelName($chName);
            $chConfigObj->setChannelNumber($chNo);
            $chConfigObj->setChannelUnit($chUnit);
            $chConfigObj->setChannelStation($chSubStation);
            if(empty($prescribedLimit)){
                $prescribedLimit = 0;
            }
            $chConfigObj->setPrescribedLimit($prescribedLimit);
            if(!empty($chseq)){
                $channlConfigs[intval($chseq)] = $chConfigObj;
            }else{
                array_push($channlConfigs,$chConfigObj);
            }
            if(!empty($chNo)){
                 if(!in_array($chNo,$chNoArr)){
                    array_push($chNoArr,$chNo);
                }else{
                    array_push($dupNoChArr,$chNo);
                }
             }

            $errMsg .=  validator::validateNumeric("Row No. $count - Channel Number",$chNo,11,false);
            $errMsg .=  validator::validateform("Row No. $count - Channel Name",$chName,255,false);
            $errMsg .=  validator::validateform("Row No. $count - Channel Unit",$chUnit,10,false);
            $count ++;
          }

          if(count($dupNoChArr)> 0){
              $errMsg .= "Duplicate Channel Number(s) :- ". implode(",",$dupNoChArr);
          }
      }
      if(!empty($errMsg)){
          $div = " <div class='ui-widget'>
                   <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                           <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                           <strong>Error during Edit Meta</strong> <br/>" . $errMsg . "</p>
                   </div></div>" ;
      }else{
              $CDS->saveList($channlConfigs);
              $folderSeq = intval($_POST["selectedFolderSeq"]);
              $channlConfigs = $CDS->FindByFolder($selSeq);
              $msg="Parameters Updated Successfully.";
                 $div = "<div class='ui-widget'>
                           <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'>
                                   <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                                   <strong>Message:</strong>&nbsp;" . $msg . "</p>
                           </div></div>";
          }
}

function checkChNoUniqueValidation(){
    $chSeqs = $_POST["chseq"];
    $chNumbers = array();
    $count = 1;
    foreach($chSeqs as $chseq){
         $chNo = $_POST["chno".$count];
         if(in_array($chNo,$chNumbers)){
             return false;
         }
         array_push($chNumbers,$chNo);
         $count++;
    }
    return true;
}
?>
<html>
    <head>
        <? include("_jsAdminInclude.php");?>
        <?include("../_InspiniaInclude.php");?>
    </head>
    <body>
    <div id="wrapper">
        <? include("leftButtons.php");?>
        <div class="wrapper wrapper-content animated fadeInRight">
            <?php echo($div)?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Edit Parameters</h5>
                        </div>
                        <div class="ibox-content">
                             <form name="form1" id="form1" method="post" action="editParameter.php">
                                <input type="hidden" name="call" id="call">
                                <div class="row">
                                     <div class="form-group">
                                        <label class="col-sm-1 control-label">Station</label>
                                        <div class="col-sm-11">
                                            <? echo DropDownUtils::getFoldersDropDownWithStationName($folders,"F_DropDown","editParameters()",$selSeq) ?>
                                        </div>
                                    </div>
                                </div>
                             </form>

                            <form name="chform" id="chform" method="post" action="editParameter.php" class="form-inline">
                                <input type="hidden" name="action" id="action">
                                <input type="hidden" name="selectedFolderSeq" value="<?echo$selSeq?>" >
                                <input type="hidden" name="delSeq" id = "delSeq">
                                <?if(empty($channlConfigs)){?>
                                        <span class="label label-info">Select Staion for populate parametes</span>
                                <?}else{?>
                                <div id = "chTable">
                                <!--<table id="chTable" class="table">-->

                                     <!--<thead>
                                         <tr>
                                           <td style="width:20%">Channel No</td>
                                           <td style="width:20%">Channel Name</td>
                                           <td style="width:20%">Channel Unit</td>
                                           <td style="width:20%">Sub Station</td>
                                           <td style="width:20%">Prescribed Limit</td>
                                         </tr>
                                     </thead>-->
                                     <div class="row">
                                        <div class="col-md-1"><label class="control-label">Channel No.</label></div>
                                        <div class="col-md-3"><label class="control-label">Channel Name</label></div>
                                        <div class="col-md-2"><label class="control-label">Channel Unit</label></div>
                                        <div class="col-md-3"><label class="control-label">Sub Station</label></div>
                                        <div class="col-md-2"><label class="control-label">Pres. Limit</label></div>
                                        <div class="col-md-1"><label class="control-label">Action</label></div>
                                     </div>
                                     <?$index = 0;?>

                                     <?foreach($channlConfigs as $ch){
                                         $index++;?>
                                         <input name="chseq[]" type="hidden" value="<?echo $ch->getSeq()?>">
                                         <div class="row" id="row<?echo $index?>" style="border-bottom:1px solid #e7eaec;padding-bottom:4px;margin-bottom:4px;">
                                             <span class="col-md-1">
                                                <input class="form-control" name="chno<?echo $index?>" type="text" size="5" value="<?echo $ch->getChannelNumber()?>" >
                                             </span>
                                             <span class="col-md-3">
                                                <input class="form-control" name="chName<?echo $index?>" type="text" size="25" value="<?echo $ch->getChannelName()?>" >
                                             </span>
                                             <span class="col-md-2">
                                                <input class="form-control" name="chUnit<?echo $index?>" type="text" size="15" value="<?echo $ch->getChannelUnit()?>" >
                                             </span>
                                             <span class="col-md-3">
                                                <input class="form-control" name="substation<?echo $index?>" type="text" size="25" value="<?echo $ch->getChannelStation()?>" >
                                             </span>
                                             <span class="col-md-2">
                                                <input class="form-control" name="prescribedlimit<?echo $index?>" type="text" size="15" value="<?echo $ch->getPrescribedLimit()?>" >
                                             </span>
                                             <?if(!$isdataExist){?>
                                              <span class="col-md-1">
                                                <a href='javascript:Delete(<?echo $ch->getSeq()?>)' title='Delete'>
                                                    <button class="btn btn-danger dim btn-sm" type="button"><i class="fa fa-times"></i> </button>
                                                </a>
                                              </span>
                                              <?}?>
                                         </div>
                                     <?}?>

                                </div>
                                 <div class="form-group">
                                        <div class="col-md-12">
                                            <button class="btn btn-primary" onclick="update()" type="submit"><i class="fa fa-check"></i>&nbsp;Update</button>
                                            <button class="btn btn-w-m btn-success" onclick="addRows()" type="button" ><i class="fa fa-plus"></i>&nbsp;Add Row</button>
                                            <button class="btn btn-white" type="reset" onclick="cancel()">Cancel</button>
                                        </div>
                                 </div>
                                 <?}?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>
 <script language="javascript">
    var index = "<?echo $index?>";

    function addRows(){
        index = parseInt(index) + 1;
        var html = '<div class="row" id="row' + index + '"><input class="form-control"  name="chseq[]" type="hidden"><span class="col-md-1"><input class="form-control"  name="chno'+ index + '" type="text" size="5"></span>';
                    html +='<span class="col-md-3"><input class="form-control"  name="chName'+ index + '" type="text" size="20"></span>';
                    html +='<span class="col-md-2"><input class="form-control"  name="chUnit'+ index + '" type="text" size="15"></span>'
                    html +='<span class="col-md-3"><input class="form-control"  name="substation'+ index + '" type="text" size="15"></span>'
                    html +='<span class="col-md-2"><input class="form-control"  name="prescribedlimit'+ index + '" type="text" size="15"></span>'

                    html +='<span class="col-md-1"><a href="javascript:Delete(0)" title=Delete><button class="btn btn-danger dim btn-sm" type="button"><i class="fa fa-times"></i> </button></a></span>'
                    html +='</tr>';

       $('#chTable').append(html);

    }
    function editParameters(){
         $("#call").val("edit");
         $("form[name='form1']").submit();
    }
    function update(){
         $("#action").val("update");
         $("form[name='form1']").submit();
    }
    function Delete(seq){
        bootbox.confirm("Are you sure, you want to remove this row?", function(result) {
            if(result){
                $('#row' + index).remove();
                 index = parseInt(index) - 1 ;
                 if(seq > 0){
                    $("#call").val("delete");
                    $("#delSeq").val(seq);
                    $.ajax({
                        type: 'POST',
                        url: "editParameter.php",
                        data: {
                            call: "delete",
                            delSeq: seq,
                        },
                        complete: function () {
                            $("#jqxgrid").jqxGrid('updatebounddata');
                        }
                    });
                 }
             }
         });
    }
</script>