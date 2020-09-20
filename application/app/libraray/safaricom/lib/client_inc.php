<?php

/*
 * Client configuration parameters, e.g various endpoints, logging flags, bulk requests limit, etc
 */ 
 
//startSmsNotification Configs
$kmp_start_stop_sms_notification_service_endpoint="http://10.66.49.198:8310/SmsNotificationManagerService/services/SmsNotificationManager"; //endpoint on SDP
$kmp_start_stop_sms_notification_service_log_soap_messages=1; // log request and responses to the server
$kmp_start_stop_sms_notification_service_log_file_prefix="/tmp/StartStopSmsNotification_"; //log file prefix, timestamp will be appended to log file
$kmp_start_stop_sms_notification_service_debug=0; // 1 - debug string to be included, 0 do not include debug str. 

//sendSMS Configs
$kmp_send_sms_service_endpoint="http://10.66.49.198:8310/SendSmsService/services/SendSms"; //endpoint on SDP
$kmp_send_sms_notify_delivery=1; // 1 - send delivery receipts to $kmp_sms_notify_service_endpoint, 0 do not send
$kmp_send_sms_notify_service_endpoint="http://10.65.12.12/kmp/kempes/notifyservice.php";
$kmp_send_sms_service_max_recipient=10; //maximum number of recipients in a single send sms request
$kmp_send_sms_service_log_soap_messages=1; // log request and responses to the server
$kmp_send_sms_service_log_file_prefix="/tmp/SendSms_"; //log file prefix, timestamp will be appended to log file
$kmp_send_sms_service_debug=0; // 1 - debug string to be included, 0 do not include debug str. The $kmp_send_sms_service_log_soap_messages must be set to 1


//getSmsDeliveryStatus Configs
$kmp_get_sms_delivery_status_service_endpoint="http://10.66.49.198:8310/SendSmsService/services/SendSms"; //endpoint on SDP
$kmp_get_sms_delivery_status_service_log_soap_messages=1; // log request and responses to the server
$kmp_get_sms_delivery_status_service_log_file_prefix="/tmp/SmsDeliveryStatus_"; //log file prefix, timestamp will be appended to log file
$kmp_get_sms_delivery_status_service_debug=0; // 1 - debug string to be included, 0 do not include debug str.

?>