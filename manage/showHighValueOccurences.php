<?
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//HighValueRuleReminderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
  require($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");
  require($ConstantsArray['dbServerUrl'] . "Utils//ExportUtils.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");

Session_start();
$managerSession = $_SESSION["managerSession"];
$locSeq = $managerSession['locSeq'];
$FDS = FolderDataStore::getInstance();
$folders = $FDS->FindByLocation($locSeq);

$HVRRDS = HighValueRuleReminderDataStore::getInstance();
$isError = false;
$msg = "";
$logs = "";
$folderSeq = "";
$fromDateForm = "";
$toDateForm = "";
$remindersCount = null;
if ($_POST["Submit"] <> "" && $_POST["Submit"] == "Submit"){
    $folderSeq = $_POST["folder"];
    $fromDateForm = $_POST["fromDate"];
    $toDateForm = $_POST["toDate"];
    if($folderSeq == 0){
        $msg = "Select a station from drop down<br>";
        $isError = true;
    }
    if($fromDateForm == null){
        $msg .= "Select From date<br>";
        $isError = true;
    }
    if($toDateForm == null){
        $msg .= "Select To date<br>";
        $isError = true;
    }
    $fromDate = new DateTime($fromDateForm);
    $toDate = new DateTime($toDateForm);
    $toDate = $toDate->add(new DateInterval('P1D'));

    $fromDateStr = $fromDate->format("Y/m/d  H:i:s");
    $toDateStr = $toDate->format("Y/m/d  H:i:s");

    $remindersCount = $HVRRDS->FindByFolderFromToDate($folderSeq,$fromDateStr, $toDateStr);
    $logs = $HVRRDS->getHighValueReminderLogs($folderSeq,$fromDateStr, $toDateStr);
    $logs = json_encode($logs);
}
$folDDown = DropDownUtils::getFoldersDropDown($folders,"folder","nope()",$folderSeq);
if (isset($_POST["call"]) && $_POST["call"] == "exportLogs"){    
    $folderSeq = $_POST["folderSeq"];
    $fromDateForm = $_POST["fromDate"];
    $toDateForm = $_POST["toDate"];
    $fromDate = new DateTime($fromDateForm);
    $toDate = new DateTime($toDateForm);
    $toDate = $toDate->add(new DateInterval('P1D'));

    $fromDateStr = $fromDate->format("Y/m/d  H:i:s");
    $toDateStr = $toDate->format("Y/m/d  H:i:s");
    $HVRRDS = HighValueRuleReminderDataStore::getInstance();
    $logs = $HVRRDS->getHighValueReminderLogs($folderSeq,$fromDateStr, $toDateStr);
    ExportUtils::ExportData($logs);        
}

?>
<!DOCTYPE html>
<html>
    <head>
        <? include("_jsAdminInclude.php");?>
        <?include("../_InspiniaInclude.php");?> 
    </head>
    <body>
        <div id="wrapper">   
            <? include("leftButtons.php");
                $highValueRules = null;
            ?>
            <Div id="page-wrapper" class="gray-bg">
                <h3>Select a Station to view its High Value Occurences</h3>
                <form action="showHighValueOccurences.php" method="POST" name="highOccurencesForm">
                    <table style="border-style: dashed" border="1" width="400px">
                        <tr>
                            <td width="150px" class="ui-widget-header">Select Station :</td>
                            <td class="ui-widget-content"><? echo $folDDown;?></td>
                        </tr>
                        <tr>
                            <td width="100px" class="ui-widget-header">From Date :</td>
                            <td class="ui-widget-content"><input type="text" size="20" value="<?echo $fromDateForm?>" name="fromDate" id="fromDate"></td>
                        </tr>
                        <tr>
                            <td width="100px" class="ui-widget-header">To Date :</td>
                            <td class="ui-widget-content"><input type="text" size="20" value="<?echo $toDateForm?>" name="toDate" id="toDate"></td>
                        </tr>
                        <tr>
                            <td width="100px" class="ui-widget-content"></td>
                            <td class="ui-widget-content"><input type="submit" name="Submit" value="Submit"></td>
                        </tr>
                    </table>
                </form>
                <table width="80%" border="0">
                     <tr>
                        <td>
                        <? if($isError == 1){ ?>
                            <div class='ui-widget'>
                               <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                                       <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                                       <strong>Error during date fetching :</strong> <br/><br/><? echo $msg ?></p>
                               </div></div>
                        <? } ?>
                        </td>
                   </tr>
                </table>
                <? if($isError == 0 and $remindersCount != null){ 
                    $folder = $folders[intval($folderSeq)]?>
                    <table width="80%" border="0">
                  <tr>
                    <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">High Value occurences for <?echo $folder->getFolderName()?>  of various parameters during
                    <? echo ($fromDateForm .' and '. $toDateForm);?></td>
                    </tr>
                  <tr>
                    <td class="ui-widget-content">

                     <form name="userForm" method="post" action="" >
                           <input type="hidden" name="editSeq" id="editSeq" />
                           <input type="hidden" name="formAction" id="formAction" />

                            <table width="100%" border="1" bordercolor="silver" style="border-style:dashed;border-width:thin;border:thin;border-color:#CCCCCC">
                              <tr>
                                <td width="200px" class="ui-widget-header">Parameters</td>
                              <?
                                foreach($remindersCount as $reminder){
                                    echo '<td class="ui-widget-content">'. $reminder['channelname'] .'</td>';
                                }
                              ?>
                              </tr>
                              <tr>
                                <td width="200px" class="ui-widget-header">Number of times exceedances occurred</td>
                                  <?
                                    foreach($remindersCount as $reminder){
                                        echo '<td class="ui-widget-content">'. $reminder['totalReminders'] .'</td>';
                                    }
                                  ?>
                              </tr>
                            </table>
                         </form>
                     </td>
                    </tr>
                    
                </table> 
                    </br>
                    <form name="exportLogsForm" id="exportLogsForm" method="post" action="showHighValueOccurences.php" >
                        <input type="hidden" name= "folderSeq" value="<?echo $folderSeq?>" >
                        <input type="hidden" name= "fromDate" value="<?echo $fromDateForm?>" >
                        <input type="hidden" name= "toDate" value="<?echo $toDateForm?>" >
                        <input type="hidden" name= "call" value="exportLogs" >
                        <div id="jqxgrid"></div>
                    </form>
                <? } ?>
            </Div> 
        </Div>
    </body>
</html>
  
<script type="text/javascript">
        $(document).ready(function (){
            $('#fromDate').datetimepicker({step:5,format:"m/d/Y"});
            $('#toDate').datetimepicker({step:5,format:"m/d/Y"}); 
            data = '<?echo $logs?>';
            var source =
            {
                localdata: data,
                datatype: "json",
                datafields: [
                    { name: 'Dated', type: 'date' },
                    { name: 'Mobile', type: 'string' },
                    { name: 'Email', type: 'string' },
                    { name: 'Parameter', type: 'string' },
                    { name: 'Highvalue', type: 'string' }
                ]
            };
            
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#jqxgrid").jqxGrid(
            {
                width: 800,
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                showtoolbar: true,
                sortable: true,
                altrows: true,
                enabletooltips: true,
                altrows: true, 
                theme: "energyblue",
                rendergridrows: function()
                {
                      return dataAdapter.records;     
                },               
                columns: [
                  { text: 'Dated',  datafield: 'Dated', width: 200, cellsformat: 'MM-dd-yyyy hh:mm:ss tt' },
                  { text: 'Mobile',  datafield: 'Mobile', width: 100 },
                  { text: 'Email Id',  datafield: 'Email', width: 230 },
                  { text: 'Parameter', datafield: 'Parameter', width: 120 },
                  { text: 'HighValue',  datafield: 'Highvalue',width: 150 },
                ],
                 renderstatusbar: function (statusbar) {
                    // appends buttons to the status bar.
                    var container = $("<div style='overflow: hidden; position: relative; margin: 5px;height:30px'></div>");
                    var exportButton = $("<div style='float: left;'><i class='fa fa-plus-square'></i><span style='margin-left: 4px; position: relative;'>Export</span></div>");
                 container.append(exportButton);
                 statusbar.append(container);
                 exportButton.jqxButton({  width: 65, height: 18 });                 
                 exportButton.click(function (event) {
                    exportLogs();    
                 })
                }
            });
            $(".xdsoft_timepicker").css("display", "none")
        });
        function exportLogs(){
           // matchingFormData = $("#exportLogsForm").serializeArray();
            //var url = "exportHighValueLogs.php";
           // $.post(url,matchingFormData,function( data ){
            //});
            $("#exportLogsForm").submit();
        }
    </script>

