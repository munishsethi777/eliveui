<?php
    
    require_once('IConstants.inc'); 
    require($ConstantsArray['dbServerUrl'] . "DataStoreMgr/UserDataStore.php");
    
$div = "";
if($_POST["submit"]<>"")
{
    
    $username = $_POST["username"];
    $password = $_POST["password"];
    $password= SecurityUtil::Encode($password);
    $UDS = UserDataStore::getInstance();
    $manager = $UDS->FindManagerByUsernamePassword($username, $password);

    if($manager != NULL){
            session_start();
            $arr = new ArrayObject();
            $arr['username'] = $manager->getUserName();
            $arr['seq'] = $manager->getSeq();
            $arr['locSeq'] = $manager->getLocationSeq();
            
            $_SESSION["managerSession"] = $arr;
                
                header("Location:managerTabs.php");
                $msg="Welcome";    
    }
    else
    {
                $msg="-Invalid Password"; 
                 $div = "<div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error During Admin login :</strong> <br/>" . $msg . "</p>
                       </div></div>" ;     
    }
}
?>


 
<!DOCTYPE html>
<html>
    <head>
        <link type="text/css" href="css/cupertino/jquery-ui-1.8.14.custom.css" rel="stylesheet" />
        <link type="text/css" href="css/custom.css" rel="stylesheet" />    
    </head>      
    <table align="center" width="40%" border="0">
       <tr>       
        <td style="padding:10px 10px 10px 10px;"><?php echo($div) ?></td>
       </tr>  
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;"> Manager's Login </td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="frm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">        
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                   <tr>
                    <td width="22%">Username :</td>
                    <td width="78%"><input name="username" type="username" size="30">
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td width="22%">Password :</td>
                    <td width="78%"><input name="password" type="password" size="30">
                      &nbsp;</td>
                  </tr>
                  
                  <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value=" Login " />
                         
                        <input type="reset" name="Reset" value="Reset">
                        
                    
                    </td></tr>
                     <tr>
                    <td>&nbsp;</td>
                    <!--td><a href="forgotPassword.php">Forgot Password</a>   </td-->
                    
                    
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
        if(document.frm1.adminPassword.value=="")
        {
            alert("enter the password");
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


