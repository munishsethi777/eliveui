<?php
require_once($ConstantsArray['dbServerUrl'] . "Mandrill/Mandrill.php");
require_once($ConstantsArray['dbServerUrl'] . "Managers/ReminderMgr.php");

class MailerUtils{

   
    public static function sendMandrillEmail(){

            try {
                $mandrill = new Mandrill('knMTJMqu1M6pPB5zahJ6XA');
                $message = array(
                    'html' => '<p>Example HTML content</p>',
                    'text' => 'Example text content',
                    'subject' => 'example subject',
                    'from_email' => 'noreply@ezae.in',
                    'from_name' => 'Example Name',
                    'to' => array(
                        array(
                            'email' => 'munishsethi777@gmail.com',
                            'name' => 'Recipient Name',
                            'type' => 'to'
                        )
                    ),
                    'headers' => array('Reply-To' => 'noreply@ezae.in'),
                    'important' => false,
                    'track_opens' => null,
                    'track_clicks' => null,
                    'auto_text' => null,
                    'auto_html' => null,
                    'inline_css' => null,
                    'url_strip_qs' => null,
                    'preserve_recipients' => null,
                    'view_content_link' => null,
                    'bcc_address' => 'munishsethi777@gmail.com',
                    'tracking_domain' => null,
                    'signing_domain' => null,
                    'return_path_domain' => null,
                    'recipient_metadata' => array(
                        array(
                            'rcpt' => 'munishsethi777@gmail.com',
                            'values' => array('user_id' => 'munishsethi777')
                        )
                    )
                );
                $async = false;
                $ip_pool = 'Main Pool';
                $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
                print_r($result);
                /*
                Array
                (
                    [0] => Array
                        (
                            [email] => recipient.email@example.com
                            [status] => sent
                            [reject_reason] => hard-bounce
                            [_id] => abc123abc123abc123abc123abc123
                        )

                )
                */
            } catch(Mandrill_Error $e) {
                // Mandrill errors are thrown as exceptions
                echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
                // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
                throw $e;
            }
    }
        public static function sendError($message,$subject){
            $from = "noreply@envirotechlive.com";
            $to = "baljeetgaheer@gmail.com";
            self::sendMandrillEmailNotification($message,$subject,$from,$to,$cc);
            $mobileNumber = "9814600356";
            $smsMessage = $subject . " \n " . $message ;
            ReminderMgr::sendSMS($mobileNumber,$smsMessage);
        }
        public static function sendMandrillEmailNotification($message,$subject,$from,$to,$cc=null,$bcc=null){
            $isSent = false;
            $toAddresses = explode(",",$to);
            $toArr = array();
            foreach($toAddresses as $to){
                $t = array(
                            'email' => $to,
                            'name' => $to,
                            'type' => 'to'
                        );
                 array_push($toArr,$t);       
            }
            try {
                $mandrill = new Mandrill('knMTJMqu1M6pPB5zahJ6XA');
                $async = "";
                $ip_pool = "";
                $send_at = "";
                $message = array(
                    'html' => $message,
                    'text' => $message,
                    'subject' => "TEST : - " . $subject,
                    'from_email' => $from,
                    'from_name' => 'EnvirotechLive',
                    'to' => $toArr,
                    'headers' => array('Reply-To' => $from),
                    'important' => false,
                    'track_opens' => null,
                    'track_clicks' => null,
                    'auto_text' => null,
                    'auto_html' => null,
                    'inline_css' => null,
                    'url_strip_qs' => null,
                    'preserve_recipients' => null,
                    'view_content_link' => null,
                    'bcc_address' => 'munishsethi777@gmail.com',
                    'tracking_domain' => null,
                    'signing_domain' => null,
                    'return_path_domain' => null,
                    'recipient_metadata' => array(
                        array(
                            'rcpt' => 'munishsethi777@gmail.com',
                            'values' => array('user_id' => 'munishsethi777')
                        )
                    )
                );
                $async = false;
                $ip_pool = 'Main Pool';
                $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
                $isSent = $result[0]["status"] == "sent";
                //print_r($result);
                /*
                Array
                (
                    [0] => Array
                        (
                            [email] => recipient.email@example.com
                            [status] => sent
                            [reject_reason] => hard-bounce
                            [_id] => abc123abc123abc123abc123abc123
                        )

                )
                */
            } catch(Mandrill_Error $e) {
                // Mandrill errors are thrown as exceptions
                echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
                // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
                throw $e;
            }
            return $isSent;
    }
        
}
?>
