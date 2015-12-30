<?session_start();
  $managerSession = $_SESSION["managerSession"];
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//HighValueRuleDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php"); 
  require($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");
  $msg = "";
  $HVRDS = HighValueRuleDataStore::getInstance();
  $FDS = FolderDataStore::getInstance();
  $locSeq = $managerSession['locSeq'];
  $folders = $FDS->FindByLocation($locSeq);
  $folderSeq = $_GET["folderseq"];
  if ($_POST["formAction"] <> "" && $_POST["formAction"] == "delete"){
    $HVRDS->deleteBySeq($_POST['editSeq']);
    $msg = StringUtils::getMessage("HighValue Rule","Rule deleted successfully",false);
  }
  if($_GET["call"] == "getRules"){
        if(!empty($folderSeq)){
            $folderSeqs =  $folderSeq;  
        }else{
           $folderSeqs = array_map(create_function('$o', 'return $o->getSeq();'), $folders);
           $folderSeqs = implode(",",$folderSeqs);  
        } 
        $rules = $HVRDS->FindArrByFolder($folderSeqs);
        $ruleJson = json_encode($rules);
        echo $ruleJson;
        return;
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
                $highValueRules = $HVRDS->FindByLocationSeq($locSeq);
                
                $folDDown = DropDownUtils::getFoldersDropDownWithStationName($folders,"folder","loadGrid(this.value)",'');
              ?>
              <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>List of Available High Value Rules</h5>
                            </div>
                            <div class="ibox-content">
                                <form method="post" role="form" name="userForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                                    <input type="hidden" name="locationSeq" id="locationSeq" value="<?echo $locationSeq?>" /> 
                                    <input type="hidden" name="editSeq" id="editSeq"/> 
                                    <input type="hidden" name="call" id="call" /> 
                                    <div class="form-group">
                                        <label class="col-sm-1 control-label">Location</label>
                                        <div class="col-sm-5">
                                            <? echo $folDDown?>
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
    $( document ).ready(function() {
            loadGrid(0);
    });
        
    function loadGrid(folderseq){
        var source =
        {
            datatype: "json",
            pagesize: 20,
            datafields: [
                { name: 'seq', type: 'integer' },
                { name: 'isactive', type: 'string' },
                { name: 'email', type: 'string' },
                { name: 'mobile', type: 'string' },
                { name: 'highvalue', type: 'string' },
                { name: 'frequency', type: 'string' },
                { name: 'actions', type: 'string' },
            ],
            url: 'showHighValueRules1.php?call=getRules&folderseq='+folderseq,
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
        var cellsrenderer = function (row, columnfield, value, defaulthtml, columnproperties) {
                if (value == 1) {
                    return '<div style="text-align: center; margin-top: 5px;"><i class="fa fa-check-square-o" title="Active"></i></div>';
                }
                else {
                    return '<div style="text-align: center; margin-top: 5px;"><i class="fa fa-square-o" title="InActive"></i></div>';
                }
        }
        var columnrenderer = function (value) {
                return '<div style="text-align: center; margin-top: 5px;">' + value + '</div>';       
        }
        var actions = function (row, columnfield, value, defaulthtml, columnproperties) {
                data = $('#jqxgrid').jqxGrid('getrowdata', row);
                var html = "<div style='text-align: center; margin-top: 5px;'><a href='javascript:Edit("+ data['seq'] + ")' ><i class='fa fa-pencil-square-o' title='Edit'></i></a>";
                    html += "<span style='margin-left:5px;'><a href='javascript:Delete("+ data['seq'] + ")' ><i class='fa fa-times' title='Delete'></i></a></span></div>";
                
                return html;
        }
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
              { text: 'id', datafield: 'seq' , hidden:true},
              { text: 'Active',  datafield: 'isactive', width: "8%",renderer:columnrenderer,cellsrenderer: cellsrenderer},  
              { text: 'Email',  datafield: 'email', width: "32%"},
              { text: 'Mobile',  datafield: 'mobile', width: "10%" },
              { text: 'Rule',  datafield: 'highvalue', width: "20%" },
              { text: 'Frequency',  datafield: 'frequency', width: "20%" },
              { text: 'Actions',  datafield: 'action', width: "10%", renderer:columnrenderer,cellsrenderer:actions}
            ]
        });
    }
</script>

