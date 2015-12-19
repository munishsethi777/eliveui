<?
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//HighValueRuleDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
  require($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");



$msg = "";
$HVRDS = HighValueRuleDataStore::getInstance();
if ($_POST["formAction"] <> "" && $_POST["formAction"] == "delete"){
    $HVRDS->deleteBySeq($_POST['editSeq']);
    $msg = StringUtils::getMessage("HighValue Rule","Rule deleted successfully",false);
}

?>
<!DOCTYPE html>
<html>
    <head>
    <? include("_jsAdminInclude.php");?>
    </head>
    <body>

    <? include("leftButtons.php");?>
    <?
        $highValueRules = $HVRDS->FindByLocationSeq($managerSession['locSeq']);

    ?>
    <Div class="rightAdminPanel">
        <? include("logOutButton.php"); ?>


    <table width="80%" border="0">
         <tr>
            <td style="padding:10px 10px 10px 10px;"><?php echo($msg) ?></td>
       </tr>
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">List of Available High Value Rules</td>
        </tr>
      <tr>
        <td class="ui-widget-content">

         <form name="userForm" method="post" action="" >
               <input type="hidden" name="editSeq" id="editSeq" />
               <input type="hidden" name="formAction" id="formAction" />

                <table width="100%" border="1" bordercolor="silver" style="border-style:dashed;border-width:thin;border:thin;border-color:#CCCCCC">
                  <tr>
                    <td width="10%" class="ui-widget-header">Station</td>
                    <td width="20%" class="ui-widget-header">Email</td>
                    <td width="20%" class="ui-widget-header">Mobile</td>
                    <td width="20%" class="ui-widget-header">Rule</td>
                    <td align="center" width="3%" class="ui-widget-header">Frequency</td>
                    <td align="center" width="3%" class="ui-widget-header">Active</td>
                    <td align="center" width="5%" class="ui-widget-header">Action</td>
                  </tr>
                  <? foreach($highValueRules as $highValueRule){
                         $isActive = "Off";
                         if($highValueRule->getIsActive() == "1"){
                                 $isActive = "On";
                          }
                          echo "<tr>
                            <td>". $highValueRule->getFolderName() ."</td>
                            <td>". $highValueRule->getEmail() ."</td>
                            <td>". $highValueRule->getMobile() ."</td>
                            <td>". $highValueRule->getChannelName() . " " .$highValueRule->getChannelStation()  ." with value more than ". $highValueRule->getHighValue() ."</td>
                            <td>". $highValueRule->getFrequency() ."</td>
                            <td>". $isActive ."</td>
                            <td align='center'>
                               <a href='javascript:Edit(". $highValueRule->getSeq() . ")' title='Edit'>
                                <img src='images/edit.png' border='0'/>
                               </a>
                               <a href='javascript:Delete(". $highValueRule->getSeq() . ")' title='Delete'>
                                <img src='images/delete.png'  border='0'/>
                               </a>

                            </td>
                          </tr>";
                      }
                  ?>
                </table>
             </form>
         </td>
        </tr>
    </table>

    </Div>

       <script language="javascript">
           function Edit(seq){
                 document.userForm.action = "CreateHighValueRuleForm.php";
                 document.getElementById('editSeq').value =  seq ;
                 document.userForm.submit();
           }
           function Delete(seq){
                 var r=confirm("Do you really want to delete this Rule");
                 if(r == true){
                     document.userForm.action = "showHighValueRules.php";
                     document.getElementById('editSeq').value =  seq ;
                     document.getElementById('formAction').value =  'delete' ;
                     document.userForm.submit();
                 }
           }
       </script>
    </body>
</html>


