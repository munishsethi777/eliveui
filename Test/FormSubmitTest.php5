<?php
   $flag = ture;
   $location = "test";
   $i=1;
   while($flag){
     $var = substr("$location", -1);
     if ($var == (string)$i){
        $newLocation = substr($location,0, -1);
         $newLocation = $newLocation . "_" . $i;            
     }else{
         $newLocation   = $location . "_" . $i;
     }         
     $i++;  
   }
?>
<!DOCTYPE html>
<html>
    <head>
        
    </head>
    <body>
    <form name="userForm" method="post" action="" >
        <input type="hidden" name="seqTest" id="seqTest" />
        <input type="button" name="button" onclick="javascript:Test()"  />
    </form>
       <script language="javascript"> 
           function Test(){                             
                 document.userForm.action =  "FormSubmitTest.php" ;                 
                 document.getElementById('seqTest').value =  "test";
                 alert("test"); 
                 document.userForm.submit();                   
           }
       </script>
    </body>