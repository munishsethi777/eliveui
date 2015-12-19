<?php
require_once($ConstantsArray['dbServerUrl'] ."/Utils/MailerUtils.php");
Class ReminderMgr{

    private static $reminderMgr;
    public static function getInstance(){
        if (!self::$reminderMgr)
        {
            self::$reminderMgr = new ReminderMgr();

            return self::$reminderMgr;
        }
        return self::$reminderMgr;
    }

    Public static function reminderInvoker(Folder $folder){
        $configuration = new Configuration();
        //parameter to know the max gap between last parsing done on the folder.
        $invokeMinutes = $configuration->getConfiguration(ConfigurationKeys::$reminderInvokeMinutes);
        //parameter to know the max gap between notifications
        $intervalMinutes = $configuration->getConfiguration(ConfigurationKeys::$reminderIntervalMinutes);
        $invokeMinutes = intval($invokeMinutes);
        $intervalMinutes = intval($intervalMinutes);

        $parsedSinceMinutes = self::getDatesDifferenceInMinutes($folder->getLastParsedOn(),date("Y-m-d H:i:s"));
        if($parsedSinceMinutes > 0){
            //OK we have some minutes passed with no parsing undertook
            if($parsedSinceMinutes > $invokeMinutes){
                //if reminder already sent, check interval and send again
                if($folder->getLastRemindedOn() != null){
                    $lastRemindedSince = self::getDatesDifferenceInMinutes($folder->getLastRemindedOn(),date("Y-m-d H:i:s"));
                    if($lastRemindedSince > $intervalMinutes){
                        self::sendNotification($folder);
                        FolderDataStore::getInstance()->updateLastReminderDate($folder->getSeq());
                    }

                }else{
                      self::sendNotification($folder);
                      FolderDataStore::getInstance()->updateLastReminderDate($folder->getSeq());
                }

            }else{
                //time still there to remind
            }
        }
    }


    private static function sendNotification(Folder $folder){
        $managers = UserDataStore::getInstance()->FindAllManagersByLocation($folder->getLocationSeq());
        foreach($managers as $manager){
            $mailMessage = "\r\n<br>Alert: File Upload Failure";
            $mailMessage .= "\r\n<br>Industry Name:". $folder->getDetails();
            $mailMessage .= "\r\n<br>Stn: ". $folder->getFolderName();
            $mailMessage .= "\r\n<br>". date("D, d-M-Y H:ia");
            $subject = "EnvirotechLive Station File upload Failure Notification";
            if($manager->getEmailId()){
                //self::sendEmail($manager->getEmailId(), $subject, $mailMessage);
            }
            if($manager->getMobile()){
                $mailMessage = "SMS Alert from CPCB" .$mailMessage ;
                $smsMessage = str_replace("<br>","",$mailMessage);
                self::sendSMS($manager->getMobile(),$smsMessage);
            }
        }
    }


    private static function getTimeStampFromStr($dateStr){
        $dated = date("m/d/Y H:i",strtotime($dateStr));
        $datedStamp = strtotime($dated);
        return $datedStamp;
    }

    private static function getTSDifferenceInMinutes($fromTime,$toTime){
        $diffTS = $toTime - $fromTime;
        return (int)$diffTS/60;
    }
    private static function getDatesDifferenceInMinutes($fromDate,$toDate){
        $fromTime = self::getTimeStampFromStr($fromDate);
        $toTime = self::getTimeStampFromStr($toDate);
        return self::getTSDifferenceInMinutes($fromTime,$toTime);
    }

    public static function sendEmail($email, $subject, $message){
       //$headers = "MIME-Version: 1.0" . "\r\n";
       // $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
       // $headers .= 'From: EnvirotechLive Notifications<noreply@envirotechlive.com>' . "\r\n";
       // $headers .= 'Cc: munishsethi777@gmail.com' . "\r\n";
       // mail($email,$subject ,$message,$headers);
       $from = "noreply@envirotechlive.com";
       $cc = "munishsethi777@gmail.com";
       MailerUtils::sendMandrillEmailNotification($message,$subject,$from,$email,$cc);
    }
    //discarded API for earlier package
    public static function sendSMSVaayo($receipientNo,$msg){
        $ch = curl_init();
        $user="amandeepdubey@gmail.com:9999219698";
        $receipientno=$receipientNo;
        $senderID="TEST SMS";
        curl_setopt($ch,CURLOPT_URL,  "http://api.mVaayoo.com/mvaayooapi/MessageCompose");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "user=$user&senderID=$senderID&receipientno=$receipientno&msgtxt=$msg");
        $buffer = curl_exec($ch);
        if(empty ($buffer))
        { echo " buffer is empty "; }
        else
        { echo $buffer; }
        curl_close($ch);
    }

    public static function sendSMS($receipientNo,$msg){
//http://203.212.70.200/smpp/sendsms?username=xxxx&password=xxxx&to=xxxx&from=text&text=this is test message&category=bulk
//http://203.212.70.200/smpp/sendsms?username=apitbpdemo&password=del@12345&to=9814600356,9417265865&from=E-LIVE&text=this%20is%20test%20message&category=bulk
        $ch = curl_init();
        $user="learntechapi";
        $password = "learntechapi123";

        $receipientno=$receipientNo;
        $senderID="ENLIVE";
        curl_setopt($ch,CURLOPT_URL,  "http://203.212.70.200/smpp/sendsms");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$user&password=$password&to=$receipientno&from=$senderID&text=$msg&category=bulk");
        $buffer = curl_exec($ch);
        if(empty ($buffer))
        { echo " buffer is empty "; }
        else
        { echo $buffer; }
        curl_close($ch);
    }
}
?>