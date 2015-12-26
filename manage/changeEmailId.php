<?
$msg="";
$emailMsg="";
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "admin//configuration.php");
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");

//$configuration = new Configuration();
Session_start();
$managerSession = $_SESSION["managerSession"];
$userDataStore = UserDataStore::getInstance();
$userSeq =  $managerSession['seq'];

if($_POST["submit"]<>"")
{
        $conEmail = $_POST["conEmailId"];
        $email = $_POST["emailId"];
        $msg =  validator::validateform("Email Id",$email,256,false);

         if($msg == ""){
          if($email <> $conEmail){
              $msg = "Email id does not match with confirm email id";
          }
         }




      if($msg != null && $msg != ""){
      $div = "         <div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                               <strong>Error during change email id :</strong> <br/>" . $msg . "</p>
                       </div></div>" ;
     }else{

         $userDataStore->updateManagerEmail($userSeq,$email );
         //$configuration->saveConfig(ConfigurationKeys::$adminEmailId,$email);
         $msg="Email id updated successfully";
         $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'>
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                               <strong>Message:</strong>&nbsp;" . $msg . "</p>
                       </div></div>";
     }

} else{
        $user = $userDataStore->getUserByseq($userSeq);
        $email = $user->getEmailId();

      }


?>

<!DOCTYPE html>
<html>
    <head>
        <?include("_jsAdminInclude.php");?>
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
                                <h5>Change Email Id</h5>
                            </div>  
                            <div class="ibox-content">
                                 <form name="frm1" method="post" action="changeEmailId.php" class="form-horizontal">
                                    <input type="hidden" name="submit" value="submit"/>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Email Id</label>
                                        <div class="col-lg-10">
                                            <input type="email" name="emailId"  value="<?php echo($email);?>" placeholder="Email" required="required" class="form-control"> 
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Confirm Email Id</label>
                                        <div class="col-lg-10">
                                            <input type="email" name="conEmailId" value="<?php echo($conEmail);?>" placeholder="Confirm" required="required" class="form-control"> 
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <button class="btn btn-sm btn-primary m-t-n-xs" type="submit">Submit</button>
                                        </div>
                                    </div>  
                                 </form>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </body>
</html>
