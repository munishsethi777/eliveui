<?php
  class StringUtils{
      public static $exemptedString = "exmp";
      public static function getMessage($crtlName,$msgText ,$isError){
       $div = "";
       if($isError){
           $div = "<div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error during " . $crtlName . " :</strong> <br/>" . $msgText . "</p>
                       </div></div>";
       }else{
           $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Message:</strong>&nbsp;" . $msgText . "</p> 
                       </div></div>"; 
            } 
         return $div;  
      }
  }
?>
