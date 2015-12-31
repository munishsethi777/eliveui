<?
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//HighValueRuleReminderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
  require($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");
  require($ConstantsArray['dbServerUrl'] . "Utils//ExportUtils.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");

Session_start();
$managerSession = $_SESSION["managerSession"];
$locSeq = $managerSession['locSeq'];
$LDS = LocationDataStore::getInstance();
$FDS = FolderDataStore::getInstance();
$locationSeqs = $LDS->FindLocationsByUser($managerSession["seq"]);
if(!in_array($locSeq,$locationSeqs)){
    array_push($locationSeqs,$locSeq);    
}
$folders = $FDS->FindByLocation(implode(",",$locationSeqs));

$HVRRDS = HighValueRuleReminderDataStore::getInstance();
$isError = false;
$msg = "";
$logs = "";
$folderSeq = "";
$fromDateForm = "";
$toDateForm = "";
$remindersCount = null;
if ($_POST["action"] == "populatedata"){
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
$folDDown = DropDownUtils::getFoldersDropDownWithStationName($folders,"folder","",$folderSeq);
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
        $highValueRules = null;?>
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <? if($isError == 1){ ?>
                            <div class='ui-widget'>
                               <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                                       <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                                       <strong>Error during date fetching :</strong> <br/><br/><? echo $msg ?></p>
                               </div></div>
                        <? } ?>
                        <div class="ibox-title">
                            <h5>High Value Occurences</h5>
                        </div>
                        <div class="ibox-content">
                               
                            <form action="showHighValueOccurences.php" method="POST" name="highOccurencesForm" class="form-horizontal">
                                <input type="hidden" name="action" id="action" value="populatedata">
                                 <div class="form-group">
                                    <label class="col-lg-2 control-label">Station</label>
                                    <div class="col-lg-5">
                                        <? echo $folDDown; ?>
                                    </div>
                                 </div>
                                 <div class="form-group">
                                    <label class="col-lg-2 control-label">From Date</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="fromDate" id="fromDate" placeholder="Select Date" value="<?echo $fromDateForm?>" required="required" class="form-control"> 
                                    </div>
                                 </div>
                                 <div class="form-group">
                                    <label class="col-lg-2 control-label">To Date</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="toDate" id="toDate" placeholder="Select Date"  value="<?echo $toDateForm?>" required="required" class="form-control"> 
                                    </div>
                                 </div>
                                 <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-primary" type="submit" value="Submit">Submit</button>
                                    </div>
                                 </div>
                            </form>
                            <?if($isError == 0 and $remindersCount != null){ 
                                $folder = $folders[intval($folderSeq)]?>
                                <table class="table">
                                  <tr>
                                    <td><h5>High Value occurences for <?echo $folder->getFolderName()?>  of various parameters during
                                    <? echo ($fromDateForm .' and '. $toDateForm);?></h5></td>
                                    </tr>
                                  <tr>
                                    <td>
                                            <table class = "table">
                                              <tr>
                                                <td><b>Parameters</b></td>
                                              <?
                                                foreach($remindersCount as $reminder){
                                                    echo '<td >'. $reminder['channelname'] .'</td>';
                                                }
                                              ?>
                                              </tr>
                                              <tr>
                                                <td><b>Number of times exceedances occurred</b></td>
                                                  <?
                                                    foreach($remindersCount as $reminder){
                                                        echo '<td>'. $reminder['totalReminders'] .'</td>';
                                                    }
                                                  ?>
                                              </tr>
                                            </table>
                                         
                                     </td>
                                    </tr>
                                    
                                </table>
                               
                                <form name="exportLogsForm" id="exportLogsForm" method="post" action="showHighValueOccurences.php" >
                                    <input type="hidden" name= "folderSeq" value="<?echo $folderSeq?>" >
                                    <input type="hidden" name= "fromDate" value="<?echo $fromDateForm?>" >
                                    <input type="hidden" name= "toDate" value="<?echo $toDateForm?>" >
                                    <input type="hidden" name= "call" value="exportLogs" >
                                    <div id="jqxgrid"></div>
                                </form>
                            <?}?>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>
    </body>
</html>
 <?include("../_jqxGridInclude.php");?>    
<script type="text/javascript">
        $(document).ready(function (){
         
            $('#toDate').datetimepicker({format:"m/d/Y",timepicker:false});
            $('#fromDate').datetimepicker({
                timepicker:false,
                format:'m/d/Y'
            });
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
                width: 900,
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
                  { text: 'Email Id',  datafield: 'Email', width: 250 },
                  { text: 'Parameter', datafield: 'Parameter', width: 200 },
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

