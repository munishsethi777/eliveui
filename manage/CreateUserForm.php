<?
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
   $user = new User();
   $UDS = new UserDataStore();   
   $otherLocationSeqs = "";    
   if($_POST["editSeq"] <> "" ){
        $UDS = new UserDataStore();
        $user = $UDS->FindBySeq($_POST["editSeq"]);
        $otherLocation = $user->getOtherLocationSeqs();
        if(!empty($otherLocation)){
           $otherLocationSeqs = implode(",",$otherLocation);    
        }   
   }  
   
  if($_POST["submit"]<>""){
      $locationSeq = $_POST["locSeq"];
      $fullName = $_POST["fullName"];      
      $username = $_POST["username"];
      $Password = $_POST["password"];
      //$conPassword = $_POST["conPassword"]; 
      $emailId = $_POST["emailId"];
      $active = $_POST["active"];
      $seq = $_POST["seq"];
      $otherLocations = $_POST["otherLocationSeqs"];
      
      $user->setLocationSeq($locationSeq);
      $user->setIsManager(false);
      $user->setUserName($username); 
      $user->setFullName($fullName);
      $encodedPassword = SecurityUtil::Encode($Password);  
      $user->setPassword($encodedPassword);
      $user->setConfirmPassword(SecurityUtil::Encode($conPassword));
      if(!empty($otherLocations)){
         $user->setOtherLocationSeqs(explode(",",$otherLocations));      
      }
      $user->setEmailId($emailId);
      $user->setIsActive($active);
      $user->setSeq($seq);
     //------------------------validations--------------------------------- 
    
    $messageText = "";    
    $div = "";
    $messageText = validator::validateform("User Name",$username,56,false);
    if($locationSeq == 0){
        $messageText .= "- Location is Required<br>";    
    }
    
    $messageText .= validator::validateform("Password",$Password,56,false); 
    
   // if($Password != $conPassword){
     //  $messageText .= "- Confirm Password should match with Password."; 
   // }   
    $messageText .=  validator::validateform("Email Id",$emailId,256,false); 
       //same user name validation
     
      if($messageText == ""){
            $userseq = $UDS->isExist($username);
            if($userseq != "" && $seq <> $userseq){
                $messageText = "User with this username already exists. Please choose another user name.";
            }
      }
       
        
     
     //------------------------------************----------------------------------
     
                                                                         
     
    //------------------------Show Validation or save object---------------------------------   
    if($messageText != null && $messageText != ""){
      $div = "         <div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error during save user details :</strong> <br/>" . $messageText . "</p>
                       </div></div>" ; 
    }else{ 
       //Creating User Object
      //set current date
      $user->setDateOfRegistration(Date("Y/m/d")); 
      $UDS = new UserDataStore();
      $UDS->Save($user);
      $messageText = "User Details Saved Successfully";
      $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Message:</strong>&nbsp;" . $messageText . "</p> 
                       </div></div>";
                       
      $user = new User();
    }
    //------------------------------************----------------------------------   
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
           
            <? include("leftButtons.php");
            
            $LDS = LocationDataStore::getInstance();
            $location = $LDS->FindBySeq($managerSession['locSeq']); // finding location from the current sessio
            
            ?>
            <div id="page-wrapper" class="gray-bg">
                <?php echo($div)?>
                <div class="wrapper wrapper-content animated fadeInRight">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox float-e-margins">
                                <div class="ibox-title">
                                    <h5>User Form</h5>
                                </div>  
                                <div class="ibox-content">
                                    <p>Location : <strong><?echo $location->getLocationName()?></strong></p>
                                    <form method="post" role="form" name="frm1" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                                        <input type="hidden" name="submit" value="submit"> 
                                        <input type="hidden" name="seq" id="seq" value="<?php echo ($user->getSeq());?>">
                                        <input type="hidden" name="seq" id="seq" value="<?php echo ($user->getSeq());?>" / >
                                        <input type="hidden" name="otherLocationSeqs" id="otherLocationSeqs" value="<?php echo ($otherLocationSeqs);?>" / >
                                        <input type="hidden" name="locSeq" id="locSeq" value="<?php echo ($location->getSeq());?>" / >  
                                        
                                        
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Full Name</label>
                                            <div class="col-lg-10">
                                                <input type="text" name="fullName" placeholder="Full Name" value="<?php echo($user->getFullName())?>" class="form-control"> 
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">User Name</label>
                                            <div class="col-lg-10">
                                                <input type="text" name="username" placeholder="User Name" value="<?php echo($user->getUserName());?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Password</label>
                                            <div class="col-lg-10">
                                                <input type="text" name="password" placeholder="Password" value="<?php echo(SecurityUtil::Decode($user->getPassword()))?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Email</label>
                                            <div class="col-lg-10">
                                                <input type="email" name="emailId" placeholder="Email" value="<?php echo($user->getEmailId());?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Active</label>  
                                            <div class="col-lg-10" style="margin-top: 5px;;">
                                                <?php
                                                   if($user->getIsActive() == "" || $user->getIsActive() == "1"){
                                                     $checked_On = "checked";
                                                   }else{
                                                      $checked_Off = "checked"; 
                                                   }
                                                 ?> 
                                                 <input name="active" value="true" type="radio"   <?php echo ($checked_On); ?> >On
                                                 <input name="active" value="false" type="radio"  <?php echo ($checked_Off); ?> >Off</td>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <button class="btn btn-sm btn-primary pull-right m-t-n-xs" type="submit">Create</button>
                                                <!--button class="btn btn-primary ladda-button" data-style="expand-right" id="saveButton" type="button">
                                                    <span class="ladda-label">Create</span>
                                                </button-->
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div> 
         </div>
    </body>
</html>
