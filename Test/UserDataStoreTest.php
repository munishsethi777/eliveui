<?php
  require_once("../DataStoreMgr/UserDataStore.php"); 
  require_once("../BusinessObjects/User.php");    
  $user = new User();
  $user->setUserName("baljeet");
  $user->setEmailId("baljeetgaheeer@gmail.com");
  $user->setIsActive(true);
  $user->setPassword("aa");
  $UDS = new UserDataStore();
  $UDS->Save($user);
?>
