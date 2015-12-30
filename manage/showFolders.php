<?session_start(); 
$managerSession = $_SESSION["managerSession"];
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require($ConstantsArray['dbServerUrl'] . "Utils//FileSystemUtils.php");      
require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//M2MSynchronizerDataStore.php");

$LDS = LocationDataStore ::getInstance();
$locations = $LDS->FindAll();
$locationSeq = $_GET["locationSeq"];
$FDS = FolderDataStore::getInstance();
$seq = $managerSession['seq'] ;
$call = $_GET["call"];
if(empty($call)){
    $call = $_POST["call"];    
}
if($call == "getFolders"){   
    if(!empty($locationSeq)){
        $locationSeqs = $locationSeq;
    }else{
        $locationSeqs = $LDS->FindLocationsByUser($seq);
        $lseq = $managerSession['locSeq'];
        if(!in_array($lseq,$locationSeqs)){
            array_push($locationSeqs,$lseq);    
        }
        $locationSeqs = implode(",",$locationSeqs);  
    }
    $folders = $FDS->FindJsonByLocationSeqs($locationSeqs);
    echo $folders;
    return;
}
?>   
<!DOCTYPE html>
<html>
    <head>
        <? include("_jsAdminInclude.php");
        include("../_InspiniaInclude.php");?>     
    </head>
    <body>
    <div id="wrapper">   
        <? include("leftButtons.php");?>
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Show Stations</h5>
                        </div>
                        <div class="ibox-content">
                            <form method="post" role="form" name="folderForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                                <input type="hidden" name="locationSeq" id="locationSeq" value="<?echo $locationSeq?>" /> 
                                 <input type="hidden" name="call" id="call" /> 
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">Location</label>
                                    <div class="col-sm-5">
                                        <? echo DropDownUtils::getUserLocationsDropDown($seq,"l_DropDown","loadGrid(this.value)",$locationSeq,"All Locations")?>
                                    </div>
                                </div>
                                <div id="jqxgrid"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>
<?include("../_jqxGridInclude.php");?>
 <script language="javascript">
        var showFolders_FoldersJSON = null;
        var showFolders_UsersJSON = null;
        var showFolders_UserDivId = 0;
        $( document ).ready(function() {
            loadGrid(0);
        });
        
        function loadGrid(locationSeq){
            var source =
            {
                datatype: "json",
                pagesize: 20,
                datafields: [
                    { name: 'isenable', type: 'string' },
                    { name: 'isonline', type: 'string' },
                    { name: 'isvisible', type: 'string' },
                    { name: 'foldername', type: 'string' },
                    { name: 'lastsynchedon', type: 'date' },
                    { name: 'lastremindedon', type: 'date' }
                ],
                url: 'showFolders.php?call=getFolders&locationSeq='+locationSeq,
                root: 'Rows',
                cache: false,
                beforeprocessing: function(data)
                {
                    source.totalrecords = data.TotalRows;
                },
                filter: function()
                {
                    // update the grid and send a request to the server.
                    $("#jqxgrid").jqxGrid('updatebounddata', 'filter');
                },
                sort: function()
                {
                        // update the grid and send a request to the server.
                        $("#jqxgrid").jqxGrid('updatebounddata', 'sort');
                },
            };
            var dataAdapter = new $.jqx.dataAdapter(source);
            
            $("#jqxgrid").jqxGrid(
            {
                width: "100%",
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                showtoolbar: false,
                sortable: true,
                filterable: true,
                columnsresize: true,
                altrows: true,
                enabletooltips: true,
                altrows: true, 
                theme: "energyblue",
                virtualmode: true,
                rendergridrows: function()
                {
                      return dataAdapter.records;     
                },               
                columns: [
                  { text: 'Enabled',  datafield: 'isenable', width: "8%"},
                  { text: 'Visible',  datafield: 'isvisible', width: "8%" },
                  { text: 'Status',  datafield: 'isonline', width: "10%" },
                  { text: 'Folder',  datafield: 'foldername', width: "34%" },
                  { text: 'Last Synched', datafield: 'lastsynchedon', width: "20%",cellsformat: 'MM-dd-yyyy hh:mm:ss tt' },
                  { text: 'Last Reminder',  datafield: 'lastremindedon',width: "20%",cellsformat: 'MM-dd-yyyy hh:mm:ss tt' },
                ]
            });
        }  
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
        
 </script>

