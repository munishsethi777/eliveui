<?php
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] . "/Plugin/PHPExcel.php");
    require_once($ConstantsArray['dbServerUrl'] . "/Plugin/PHPExcel/IOFactory.php");
    require_once($ConstantsArray['dbServerUrl'] . "/Utils/ConvertorUtils.php");
    require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/ChannelConfiguration.php");
Class ExportUtils{
    private static function num_to_letter($num, $uppercase = TRUE){
        $num -= 1;
        $letter =     chr(($num % 26) + 97);
        $letter .=     (floor($num/26) > 0) ? str_repeat($letter, floor($num/26)) : '';
        return         ($uppercase ? strtoupper($letter) : $letter); 
    }
    private static function getChannelName($channelsInfo,$chNo){
        foreach($channelsInfo as $channel){
            $c = new ChannelConfiguration();
            $c = $channel;
            if($c->getChannelNumber() == $chNo){
                return $c->getChannelName();
            }
        }
    }
    
    public static function exportStationGridReport($gridJsonArr, $folderName){
       $objPHPExcel = new PHPExcel();
       $rowNo = 1;
       $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowNo, 'Date & Time');

       $channelsArr = $gridJsonArr['channels'];
       $alphabetInt = 0;
       foreach ($channelsArr as $key=>$value) {
            $alphabet = self::num_to_letter($alphabetInt+2);
            $objPHPExcel->getActiveSheet()->setCellValue($alphabet.$rowNo, $key);
            $alphabetInt++;
       }
       
       $rowNo++;
       $rows = $gridJsonArr['data'];
       foreach($rows as $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowNo, $item['dated']);
            $valArray = $item['values'];
            $alphabetInt = 0;
            foreach ($valArray as $val) {
                $alphabet = self::num_to_letter($alphabetInt+2);
                $objPHPExcel->getActiveSheet()->setCellValue($alphabet.$rowNo, $val);
                $alphabetInt++;
            }
            $rowNo++;
       }
       $dateTime = new DateTime();
       $fileName = "CSVFiles/". $folderName ."_" . $dateTime->format("Y-m-d_H-i-s") . ".csv" ;
       $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV'); 
       $path = $ConstantsArray['dbServerUrl'] . $fileName;          
       $objWriter->save($path);
        if(file_exists($path)){
          header("Content-Type: application/download",false);
          header ("Content-Disposition: attachment; filename=" . $fileName,false);
          header("Content-Length: " . filesize($fileName));   
          $fp = fopen($path,"r");
          fpassthru($fp);
        } 
    }
    
    public static function exportMultiStationGridReport($gridJsonArr,$exportType){
       $objPHPExcel = new PHPExcel();
       
       $channelsArr = $gridJsonArr['channels'];
       $col = 0;
       $row = 1; // ROWS starts from 1
       $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row, "Dated");
       $col++;
       //set headers here
       try{
           foreach ($channelsArr as $ch){
                $thVal = str_replace("<br>"," ",$ch);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row,$thVal);
                $col++;
           }
       }catch(Exception $e){
            $e; 
       }
       //set data here
       $rows = $gridJsonArr['data'];
       $row = 2; 
       try{
           foreach($rows as $key=>$value) {
                $col = 0;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row,$key);
                foreach ($value as $val) {
                    $col++;
                    if($val != "" && $val != "n.o" && $val != "n.a"){
                        try{
                            $val = number_format($val,2);
                        }catch(Exception $e){}
                    }
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row,$val);
                }
                $row++;
           }
       }catch(Exception $e){
           $e;
       }
       $dateTime = new DateTime();
       if($exportType == "pdf"){
           $objPHPExcel = self::setPDFStyle($objPHPExcel);
           $fileName = "MultiStation_" . $dateTime->format("Y-m-d_H-i-s") . ".pdf" ;
           header('Content-Type: application/download');
           header('Content-Disposition: attachment;filename="'.$fileName.'"');
           header('Cache-Control: max-age=0');
           $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
           $objWriter->save('php://output');
           
       }else if($exportType == "csv"){
           $fileName = "MultiStation_" . $dateTime->format("Y-m-d_H-i-s") . ".csv" ;
           header('Content-Type: application/download');
           header('Content-Disposition: attachment;filename="'.$fileName.'"');
           header('Cache-Control: max-age=0');
           $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
           $objWriter->save('php://output');
       
       }else if($exportType == "html"){
           $objPHPExcel = self::setHTMLStyle($objPHPExcel);
           $fileName = "MultiStation_" . $dateTime->format("Y-m-d_H-i-s") . ".html" ;
           header('Content-Type: application/download');
           header('Content-Disposition: attachment;filename="'.$fileName.'"');
           header('Cache-Control: max-age=0');
           $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
           $objWriter->save('php://output');
       
       }else if($exportType == "xls"){
           $objPHPExcel = self::setHTMLStyle($objPHPExcel);
           $fileName = "MultiStation_" . $dateTime->format("Y-m-d_H-i-s") . ".xls" ;
           header('Content-Type: application/download');
           header('Content-Disposition: attachment;filename="'.$fileName.'"');
           header('Cache-Control: max-age=0');
           $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
           $objWriter->save('php://output');
       }   
       
    }
    private static function setPDFStyle(PHPExcel $objPHPExcel){
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setScale(50);
        
        $highCol = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $highRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        //set header back color
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFill()->getStartColor()->setRGB('0099FF');
        //set dates back color
        $objPHPExcel->getActiveSheet()->getStyle('A2:A'.$highRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
        $objPHPExcel->getActiveSheet()->getStyle('A2:A'.$highRow)->getFill()->getStartColor()->setRGB('F0F0F0 ');
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.$highRow)->getFont()->setSize(7);
        $objPHPExcel->getActiveSheet()->setShowGridlines(false);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_NONE);
        return $objPHPExcel;        
    }
    private static function setHTMLStyle(PHPExcel $objPHPExcel){
        //$sharedStyle1 = new PHPExcel_Style();
//        $default_border = array(
//            'style' => PHPExcel_Style_Border::BORDER_THIN,
//            'color' => array('rgb'=>'1006A3')
//        );
//        $style_header = array(
//            'borders' => array(
//                'bottom' => $default_border,
//                'left' => $default_border,
//                'top' => $default_border,
//                'right' => $default_border,
//            ),
//            'fill' => array(
//                'type' => PHPExcel_Style_Fill::FILL_SOLID,
//                'color' => array('rgb'=>'E1E0F7'),
//            ),
//            'font' => array(
//                'bold' => true,
//            )
//        );
        //header decorations;
        $highCol = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $highRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFill()->getStartColor()->setRGB('0099FF');
        //set dates back color
        $objPHPExcel->getActiveSheet()->getStyle('A2:A'.$highRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
        $objPHPExcel->getActiveSheet()->getStyle('A2:A'.$highRow)->getFill()->getStartColor()->setRGB('F0F0F0 ');
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFont()->getColor()->setRGB('FFFFFF');
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getAlignment()->setWrapText(true);
        //cells formatting;
        $styleArray = array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
            )
        );
        for($i=2;$i<=$highRow;$i++){
            $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':'.$highCol.$i)->applyFromArray($styleArray);
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(17);
        return $objPHPExcel;

    }
    public static function exportCSV($rows,$channelNos,$folderName,$isPLimits,$ChannelsInfo){
        $channelNosArr = explode(",", $channelNos);
        $objPHPExcel = new PHPExcel();
        $rowNo = 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowNo, 'Date & Time');
        for ($i = 0, $l = count($channelNosArr); $i < $l; ++$i) {
            $alphabet = self::num_to_letter($i+2);
            $channelName = self::getChannelName($ChannelsInfo,$channelNosArr[$i]);
            $objPHPExcel->getActiveSheet()->setCellValue($alphabet.$rowNo, $channelName);
        }
        
                                      
        $rowNo++;
        foreach($rows as $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowNo, $item[0]);
            for ($i = 0, $l = count($channelNosArr); $i < $l; ++$i) {
                $alphabet = self::num_to_letter($i+2);
                $chValue =  $item['ch'.$channelNosArr[$i].'value'];
                $channelName = self::getChannelName($ChannelsInfo,$channelNosArr[$i]);
                if($isPLimits == 1){
                    $chValue = ConvertorUtils::getPrescribedValue($channelName, $chValue);
                    $unit = ConvertorUtils::getPrescribedUnit($channelName);
                }
                
                $objPHPExcel->getActiveSheet()->setCellValue($alphabet.$rowNo, $chValue);
            }
            $rowNo++;
       }
       $dateTime = new DateTime();
       $fileName = "CSVFiles/". $folderName ."_" . $dateTime->format("Y-m-d_H-i-s") . ".csv" ;
       $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV'); 
       $path = $ConstantsArray['dbServerUrl'] . $fileName;          
       $objWriter->save($path);
       
       
        if(file_exists($path)){
          header("Content-Type: application/download",false);
          header ("Content-Disposition: attachment; filename=" . $fileName,false);
          header("Content-Length: " . filesize($fileName));   
          $fp = fopen($path,"r");
          fpassthru($fp);
        }
    }
    
    public static function ExportData($rows){
        $objPHPExcel = new PHPExcel();

// Set document properties
        $objPHPExcel->getProperties()->setCreator("Manger")
                                     ->setLastModifiedBy("Manger")
                                     ->setTitle("Office 2007 XLSX Test Document")
                                     ->setSubject("Office 2007 XLSX Test Document")
                                     ->setDescription("High Value Logs")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("High Value Logs");


                                     
       
            $alphas = range('A', 'Z');
            $rowCount = 1;
            $count = 0;
            foreach($rows as $row){
                $i = 0;       
                foreach($row as $col=>$value){                       
                    if($count < 3){
                        $count = 1;
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alphas[$i] . $count, $col);
                        $count++;
                    }
                    $colName = $alphas[$i]. $count;
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, $value);
                    $i++; 
                }
                $count++;
                $rowCount++;
            }



        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('High Value Logs');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="HighValueLogs.csv"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        $objWriter->save('php://output');  
    }
}
?>
