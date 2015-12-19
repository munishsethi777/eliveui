<?include("sessioncheck.php");?>
<?
$messageText="";
if($_POST["submit"]<>"")
{
    
    $earlierPassword = $_POST["earlierPassword"];
    $newPassword = $_POST["newPassword"]; 
    $confirmNewPassword = $_POST["confirmNewPassword"]; 
    
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");
    require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//UserDataStore.php"); 
    require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");  
    $user = $_SESSION["userlogged"];
    $oldPassword = $user->getDecodedPassword();
    
    $UDS = UserDataStore::getInstance(); 
    $div = "";
    $messageText = validator::validateform("Earlier Password",$earlierPassword,256,false);
    if($messageText != null && $messageText != ""){
          $messageText = $messageText . "<br/>". validator::validateform("New Password",$newPassword,256,false); 
    }else{
      $messageText =  validator::validateform("New Password",$newPassword,256,false);
    }
    
     if($messageText != null && $messageText != ""){
          $messageText = $messageText . "<br/>". validator::validateform("Confirm Password",$confirmNewPassword,256,false); 
    }else{
      $messageText =  validator::validateform("Confirm New Password",$confirmNewPassword,256,false);
    }
    
    if($messageText == ""){
        if($newPassword != $confirmNewPassword){
        $messageText="-New password and confirm password does not match";
       
        }
        
    if($oldPassword != $earlierPassword){ 
        if($messageText != null && $messageText != ""){
          $messageText = $messageText . "<br/>". "-Earlier Password does not match with the one in the database";
        }else{
           $messageText =  "-Earlier Password does not match with the one in the database";
         }
       }
    }  
    
   
    if($messageText != null && $messageText != ""){
         $div =  StringUtils::getMessage("Change Passowrd" ,$messageText,true);
    }else{
         $newPassword = SecurityUtil::Encode($newPassword);
         $user->setPassword($newPassword);   
         $UDS->updatePassword($user);
         $messageText="Password updated successfully";
         $div = StringUtils::getMessage("" ,$messageText,false);
     }
}
?>


 
<!DOCTYPE html>
<html>
    <head>
      
    </head>
    <body>
    
    <?include("leftButtons.php");?>
    
    <Div class="rightAdminPanel">
       
    
         
    <table width="80%" border="0">
       <tr>       
        <td style="padding:10px 10px 10px 10px;"><?php echo($div) ?></td>
       </tr>  
      <tr>
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Change Password </td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="frm1" method="post" action="ChangeUserPassword.php">    
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                  <tr>
                    <td width="22%">Earlier Password :</td>
                    <td width="78%"><input name="earlierPassword" type="password" size="50">
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td>New Password :</td>
                    <td><input name="newPassword" type="password" size="50">  
                      &nbsp;</td>
                  </tr>
                   <tr>
                    <td>Confirm New Password :</td>
                    <td><input name="confirmNewPassword" type="password" size="50">  
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>
                         <input type="submit" name="submit" value="submit">
                        <input type="reset" name="Reset" value="Reset">
                    
                    </td>
                  </tr>
                </table>
              </form> 
         </td>
        </tr>
        
    </table>

    
    
    
    
    </Div>
     
      
      <script language="javascript">
function submitform()
{
    if(document.frm1.earlierPassword.value=="")
    {
        alert("enter your old password");        
        return false;
    }
    
    if(document.frm1.newPassword.value=="")
    {
        alert("enter new password");
        return false;
    }    
    if(document.frm1.confirmNewPassword.value=="")
    {
        alert("enter confirm password");
        return false;
    }
    
    else
    {
        return true;
    }
    

}
</script>
    </body>
</html>