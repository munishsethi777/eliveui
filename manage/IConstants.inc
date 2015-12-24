<?php                                        
  include('D:/projects/eliveProject/eliveui/log4php/Logger.php');
  Logger::configure('D:/projects/eliveProject/eliveui/log4php/log4php.xml');                                          
  $ConstantsArray = array('dbServerUrl' => 'D:/projects/eliveProject/eliveui/',

  'applicationURL'=>'http://localhost:8080/eliveui/',
   'logger' => 'myDBLogger'

  );

  $PERMISSION_MANAGER = "Manager";

  $PERMISSION_USER = "User";

  //FOLLOWING IS THE SERVER URL, COMMENT THIS PATH BEFORE LOCAL USAGE AND SET LOCAL PATH IN ARRAY ABOVE

  //$ConstantsArray = array('dbServerUrl' => '/home/oorjaint/public_html/mbb/'); 

  

?>