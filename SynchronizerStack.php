<?
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
  
  $limit = 500;
  $locationSeqs = $_GET["locs"];
  $lastSeq = $_GET['lastSeq'];
   
   
  $XML = "<?xml version='1.0' encoding='UTF-8'?>";
  $parentTag = "ELiveFullDataCall";
  
  $XML .= '<'. $parentTag .' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">';   
  if($lastSeq != ""){
        $WQDStackDS = WQDStackDataStore::getInstance();
        $files = $WQDStackDS->getWQDDataByLocationSeqsAndLastSeqs($locationSeqs,$lastSeq,$limit);
        $XML .= "<WQDFilesData>";
        foreach($files as $file){
            $XML .= getWQDDataXML($file);
        }
        $XML .= "</WQDFilesData>"; 
  }
      
  $XML .= "</". $parentTag .">";
  header('Content-Type: text/xml');
  //header("HTTP/1.0 200 OK");
  echo $XML;
  die;
  
  

  
  function getWQDDataXML($row){
        $XML .= "<wqdfiledata>";
            $XML .= "<wqdfiledataseq>". $row["wqdfiledataseq"] ."</wqdfiledataseq>";
            $XML .= "<wqdfolderseq>". $row["wqdfolderseq"] ."</wqdfolderseq>";
            $XML .= "<wqdfiledatadated>". $row["wqdfiledatadated"] ."</wqdfiledatadated>";
            $XML .= "<wqdfiledatareportno>". $row["wqdfiledatareportno"] ."</wqdfiledatareportno>";
            $XML .= "<wqdfiledatachecksum>". $row["wqdfiledatachecksum"] ."</wqdfiledatachecksum>";
            
            for($i=1;$i<=15;$i++){
                $val =  $row["ch".$i."value"];
                $status = $row["ch".$i."status"];
                $xsi = getXSI($val);
                $XML .= "<ch".$i."value ". $xsi .">". $val ."</ch".$i."value>";
                $XML .= "<ch".$i."status ". $xsi .">". $status ."</ch".$i."status>";
            }               
        $XML .= "</wqdfiledata>";
        return $XML;
  }
  function getXSI($val){
    $xsi = "";
    if($val == ""){
        $xsi = "xsi:null=\"true\"";
    }
    return $xsi;  
  }
  
?>