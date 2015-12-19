<?
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/User.php");
    $lsp = $_GET['lsp'];
   $FDS = FolderDataStore::getInstance();
   $folders = $FDS->FindByLocation($lsp);


?>
<script>
  $(function() {
        $('.dynamicData').click(function() {
             $('<form action="cpcbDynamicData.php" method="POST"/>')
                .append($('<input type="hidden" name="locSeq" value="' + this.id + '">'))
                .appendTo($(document.body)) //it has to be added somewhere into the <body>
                .submit();
        });
        $(".menuDiv a").button();

        $("#<?echo $menuItem?>").addClass("activeMenu");

 });
</script>
  <style>
    .activeMenu{
        border: 1px solid #d9d6c4 !important;
        background: #007BC7 url("admin/css/flatblue/images/ui-bg_fine-grain_0_007BC7_60x60.png") 50% 50% repeat !important;
        font-weight: normal !important;
        color: #ffffff !important;
    }
    .menuHoverFix{
        border: 1px solid #448dae !important;
        background: #6eac2c !important;
        color: #ffffff !important;
    }
    .menuOuterDiv{
        /*width:100%;*/
        padding:3px 0px 3px 0px;
    }
    .menuDiv{
      width:1200px;
      border:0px solid silver;
      margin:auto;
    }
    .menuDiv a{
      margin:0px 5px 0px 0px;
      padding:4px 20px 4px 20px;
      display:inline-block;
      font-size:11px;
    }
    .menuDiv a:hover,.menuActive{
      color:#EEE;
    }
    .menuDiv .ui-button-text-only .ui-button-text {
       padding:0px;
    }

  </style>
  
<?
$userDataStore = UserDataStore::getInstance();
$user= $_SESSION["userlogged"];
$userSeq =  $user->getSeq();
$stationtypes = $userDataStore->getAllStationType($userSeq);?>
<div class="menuOuterDiv ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header">
<div class="menuDiv">
<?//if($lsp  == null){?>
    <a id="mapMenu" href="cpcbMap.php">Live Map</a>
    <?foreach($stationtypes as $type){
        if($type == "aqms"){
            echo '<a id="reportAQMSMenu" href="cpcbReportMultiStation.php">AQMS</a>';
        }else if($type == "stack"){
            echo '<a id="reportCEMSMenu" href="cpcbStackReportMultiStation.php">CEMS</a>'; 
        }else if($type == "effluent"){    
            echo '<a id="reportEFFLUENTMenu" href="cpcbEffluentReportMultiStation.php">EFFLUENT</a>';     
        }     
    }?>
   <a id="comments" href="cpcbValidationExemptionsComments.php">Data Valdations & Comments</a>
<?/**}else{
        $isAqms = false;
        $isStack = false;
        $isEffluent = false;

        foreach($folders as $folder){
            $type = $folder->getStationType();
            if($type == "aqms" && !$isAqms){
                $isAqms = true;
                echo ('<a id="reportAQMSMenu" href="cpcbReportMultiStation.php?lsp='. $lsp .'">AQMS</a>');
            }elseif($type == "stack" && !$isStack){
                $isStack = true;
                echo ('<a id="reportCEMSMenu" href="cpcbStackReportMultiStation.php?lsp='. $lsp .'">CEMS</a>');
            }elseif($type == "effluent" && !$isEffluent){
                $isStack = true;
                echo ('<a id="reportEFFLUENTMenu" href="cpcbEffluentReportMultiStation.php?lsp='. $lsp .'">EFFLUENT</a>');
            }
        }
    */?>
    <!--<a id="comments" href="cpcbValidationExemptionsComments.php<? echo ("?lsp=". $lsp); ?>">Data Valdations, Exemptions & Comments</a> -->
<?//}?>
<?
    session_start();
    $userLogged = new User();
    $userLogged = $_SESSION["userlogged"];
    $userName = "";
    if($userLogged){
        $userName = $userLogged->getUserName();
    }


if($userName  == "cpcb"){?>
    <a id="exemption" href="cpcbExemptions.php">Data Exemption  </a>
<?}?>
<?
    session_start();
    if(isset($_SESSION["userlogged"])){
        $user = $_SESSION["userlogged"];
        echo ("<a class='activeMenu' style='float:right' href='logout.php'><b>". $user->getUserName() ."</b> - Logout</a>");
    }
?>
</div>
</div>