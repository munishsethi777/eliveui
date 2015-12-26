<?php
   include('../log4php/Logger.php');
   Logger::configure('../log4php/log4php.xml');
  $ConstantsArray = array('dbServerUrl' => 'D:/projects/eliveProject/app/',
  'baseUrl' => 'D:/projects/eliveProject/',
  'applicationURL'=>'http://localhost:8080/',
   'logger' => 'myDBLogger'
 );

//   $ConstantsArray = array('dbServerUrl' => '/home/enviro/public_html/site/',
// 'dbServerUrl' => '/home/enviro/public_html/'
//  'applicationURL'=>'http://www.envirotechlive.com/app/'
//  );
  $PERMISSION_MANAGER = "Manager";
  $PERMISSION_USER = "User";

  $Log4PHP_MyDBLogger = "myDBLogger";
  $Log4PHP_MyMailerLogger = "myMailerLogger";
  $Log4PHP_MyFileLogger = "myFileLogger";
  //FOLLOWING IS THE SERVER URL, COMMENT THIS PATH BEFORE LOCAL USAGE AND SET LOCAL PATH IN ARRAY ABOVE
  //$ConstantsArray = array('dbServerUrl' => '/home/oorjaint/public_html/mbb/');

?>