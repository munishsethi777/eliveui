<?php 
  include('../log4php/Logger.php');
  Logger::configure('../log4php/log4php.xml');                                         
  $ConstantsArray = array('dbServerUrl' => 'D:/projects/eliveProject/app/',
  'applicationURL'=>'http://localhost:8080/app',
  'rootURL'=>'http://localhost:8080/',
  'repositoryURL'=>'D:/projects/eliveProject/Repository/',
  'logger' => 'myDBLogger'
  );
  $PERMISSION_MANAGER = "Manager";
  $PERMISSION_USER = "User";
  //FOLLOWING IS THE SERVER URL, COMMENT THIS PATH BEFORE LOCAL USAGE AND SET LOCAL PATH IN ARRAY ABOVE
  //$ConstantsArray = array('dbServerUrl' => '/home/oorjaint/public_html/mbb/'); 
  
?>