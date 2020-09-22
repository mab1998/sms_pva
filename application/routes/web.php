<?php

/*
|--------------------------------------------------------------------------
| AuthController
|--------------------------------------------------------------------------
|
| All authentication like client login, admin login, forgot password, Every functions
| are described in this controller
|
*/

//======================================================================
// Client Login
//======================================================================
// Route::get('/', 'AuthController@HomePage');

Route::get('/login', 'AuthController@clientLogin');
Route::post('client/get-login', 'AuthController@clientGetLogin');
Route::get('signup', 'AuthController@clientSignUp');
Route::post('user/post-registration', 'AuthController@postUserRegistration');
Route::get('user/registration-verification', 'AuthController@clientRegistrationVerification');
Route::post('user/post-verification-token', 'AuthController@postVerificationToken');
Route::get('verify-user/{token}', 'AuthController@verifyUserAccount');
Route::get('forgot-password', 'AuthController@forgotUserPassword');
Route::post('user/forgot-password-token', 'AuthController@forgotUserPasswordToken');
Route::get('user/forgot-password-token-code/{token}', 'AuthController@forgotUserPasswordTokenCode');


//======================================================================
// Admin Login
//======================================================================
Route::get('admin', 'AuthController@adminLogin');
Route::post('admin/get-login', 'AuthController@adminGetLogin');
Route::get('admin/forgot-password', 'AuthController@forgotPassword');
Route::post('admin/forgot-password-token', 'AuthController@forgotPasswordToken');
Route::get('admin/forgot-password-token-code/{token}', 'AuthController@forgotPasswordTokenCode');


//======================================================================
// Permission Check
//======================================================================

/*Permission Check*/
Route::get('permission-error','AuthController@permissionError');



//======================================================================
// Update Application
//======================================================================

Route::get('update','AuthController@verifyProductUpdate');
Route::post('update/post-verify-product','AuthController@updateApplication');
//Route::get('update','AuthController@updateApplication');
Route::get('admin/check-available-update','AuthController@checkAvailableUpdate');




/*
|--------------------------------------------------------------------------
| ClientDashboardController
|--------------------------------------------------------------------------
|
| Maintain application summery like Total accounts, support tickets, invoice created etc.
| Finally we will find a whole application overview in this controller
|
*/

Route::get('dashboard', 'ClientDashboardController@dashboard');
Route::get('logout', 'ClientDashboardController@logout');
Route::post('user/menu-open-status', 'ClientDashboardController@menuOpenStatus');




Route::get('admin/edit-profile', 'DashboardController@editProfile');
Route::post('admin/post-personal-info', 'DashboardController@postPersonalInfo');
Route::post('admin/update-avatar', 'DashboardController@updateAvatar');
Route::get('admin/change-password', 'DashboardController@changePassword');
Route::post('admin/update-password', 'DashboardController@updatePassword');
Route::post('admin/menu-open-status', 'DashboardController@menuOpenStatus');

/*
|--------------------------------------------------------------------------
| DashboardController
|--------------------------------------------------------------------------
|
| Maintain application summery like Total accounts, support tickets, invoice created etc.
| Finally we will find a whole application overview in this controller
|
*/

Route::get('admin/dashboard', 'DashboardController@dashboard');
Route::get('admin/logout', 'DashboardController@logout');

//======================================================================
// Update Application (Version 2.3)
//======================================================================
Route::get('admin/update-application', 'DashboardController@updateApplication');
Route::post('admin/post-update-application', 'DashboardController@postUpdateApplication');
Route::get('admin/backup-database', 'DashboardController@backupDatabase');

/*
|--------------------------------------------------------------------------
| ClientController
|--------------------------------------------------------------------------
|
| Store all clients/users information, edit, update, delete these in this controller.
|
*/

//======================================================================
// Client Manage
//======================================================================
Route::get('clients/all', 'ClientController@allClients');
Route::any('clients/get-all-clients-data', 'ClientController@getAllClients');
Route::get('clients/add', 'ClientController@addClient');
Route::post('clients/post-new-client', 'ClientController@addClientPost');
Route::get('clients/send-email', 'ClientController@sendEmail');
Route::post('clients/post-send-bulk-email', 'ClientController@postSendEmail');

//======================================================================
// Profile Manage
//======================================================================
Route::get('clients/view/{id}', 'ClientController@viewClient');
Route::post('clients/update-limit', 'ClientController@updateLimit');
Route::post('clients/update-image', 'ClientController@updateImage');
Route::post('clients/update-client-post', 'ClientController@updateClient');
Route::post('clients/send-sms', 'ClientController@sendSMS');

/*Export N Import wit CSV*/
Route::get('clients/export-n-import', 'ClientController@exportImport');
Route::get('clients/export-clients', 'ClientController@exportClients');
Route::get('clients/download-sample-csv', 'ClientController@downloadSampleCSV');
Route::post('clients/post-new-client-csv', 'ClientController@addNewClientCSV');

//======================================================================
// Client Delete
//======================================================================
Route::get('clients/delete-client/{id}', 'ClientController@deleteClient');


//======================================================================
// Client Group Manage
//======================================================================
Route::get('clients/groups', 'ClientController@clientGroups');
Route::post('clients/add-new-group', 'ClientController@addNewClientGroup');
Route::post('clients/update-group', 'ClientController@updateClientGroup');
Route::get('clients/export-client-group/{id}', 'ClientController@exportClientGroup');
Route::get('clients/delete-group/{id}', 'ClientController@deleteClientGroup');


/*
|--------------------------------------------------------------------------
| InvoiceController
|--------------------------------------------------------------------------
|
| Discuss in future
|
*/

Route::get('invoices/all', 'InvoiceController@allInvoices');
Route::get('invoices/recurring', 'InvoiceController@recurringInvoices');

Route::get('invoices/paid', 'InvoiceController@paidInvoice');



Route::get('invoices/add', 'InvoiceController@addInvoice');
Route::post('invoices/post-new-invoice', 'InvoiceController@postInvoice');
Route::get('invoices/view/{id}', 'InvoiceController@viewInvoice');
Route::get('invoices/edit/{id}', 'InvoiceController@editInvoice');
Route::post('invoices/post-edit-invoice', 'InvoiceController@postEditInvoice');
Route::get('invoices/client-iview/{id}', 'InvoiceController@clientIView');
Route::get('invoices/iprint/{id}', 'InvoiceController@printView');
Route::get('invoices/download-pdf/{id}', 'InvoiceController@downloadPdf');
Route::get('invoices/mark-paid/{id}', 'InvoiceController@markInvoicePaid');
Route::get('invoices/mark-unpaid/{id}', 'InvoiceController@markInvoiceUnpaid');
Route::get('invoices/mark-partially-paid/{id}', 'InvoiceController@markInvoicePartiallyPaid');
Route::get('invoices/mark-cancelled/{id}', 'InvoiceController@markInvoiceCancelled');
Route::get('invoices/iprint/{id}', 'InvoiceController@printView');
Route::post('invoices/update-invoice', 'InvoiceController@updateInvoice');
Route::post('invoices/invoice-paid', 'InvoiceController@paidInvoice');
Route::post('invoices/invoice-unpaid', 'InvoiceController@unpaidInvoice');
Route::post('invoices/invoice-cancelled', 'InvoiceController@cancelledInvoice');
Route::post('invoices/invoice-partially-paid', 'InvoiceController@partiallyPaidInvoice');
Route::get('invoices/delete-invoice/{id}','InvoiceController@deleteInvoice');
Route::get('invoices/stop-recurring-invoice/{id}','InvoiceController@stopRecurringInvoice');
Route::post('invoices/send-invoice-email','InvoiceController@sendInvoiceEmail');

/*
|--------------------------------------------------------------------------
| AdministratorController
|--------------------------------------------------------------------------
|
| Discuss in future
|
*/

//======================================================================
// Administrator Manage
//======================================================================
Route::get('administrators/all','AdministratorController@allAdministrator');
Route::post('administrators/add-new','AdministratorController@addAdministrator');
Route::get('administrators/manage/{id}','AdministratorController@manageAdministrator');
Route::post('administrators/post-update-admin','AdministratorController@postUpdateAdministrator');
Route::get('administrators/delete-admin/{id}','AdministratorController@deleteAdministrator');

//======================================================================
// Administrator Role Manage
//======================================================================
Route::get('administrators/role','AdministratorController@administratorRole');
Route::post('administrators/add-role','AdministratorController@addAdministratorRole');
Route::post('administrators/update-role','AdministratorController@updateAdministratorRole');
Route::get('administrators/set-role/{id}','AdministratorController@setAdministratorRole');
Route::get('administrators/delete-role/{id}','AdministratorController@deleteAdministratorRole');
Route::post('administrators/update-admin-set-roles','AdministratorController@updateAdministratorSetRole');



/*
|--------------------------------------------------------------------------
| SupportTicketController
|--------------------------------------------------------------------------
|
| Discuss in later
|
*/

Route::get('support-tickets/all','SupportTicketController@all');
Route::get('support-tickets/create-new','SupportTicketController@createNew');
Route::get('support-tickets/view-ticket/{id}','SupportTicketController@viewTicket');
Route::get('support-tickets/department','SupportTicketController@department');
Route::get('support-tickets/view-department/{id}','SupportTicketController@viewDepartment');
Route::get('support-tickets/ticket-department/{id}','SupportTicketController@ticketDepartment');
Route::get('support-tickets/ticket-status/{id}','SupportTicketController@ticketStatus');
Route::post('support-tickets/post-department','SupportTicketController@postDepartment');
Route::post('support-tickets/update-department','SupportTicketController@updateDepartment');
Route::post('support-tickets/post-ticket','SupportTicketController@postTicket');
Route::post('support-tickets/ticket-update-department','SupportTicketController@updateTicketDepartment');
Route::post('support-tickets/ticket-update-status','SupportTicketController@updateTicketStatus');
Route::post('support-tickets/replay-ticket','SupportTicketController@replayTicket');
Route::get('support-tickets/delete-ticket/{id}','SupportTicketController@deleteTicket');
Route::get('support-tickets/delete-department/{id}','SupportTicketController@deleteDepartment');
Route::post('support-ticket/basic-info-post','SupportTicketController@postBasicInfo');
Route::post('support-ticket/post-ticket-files','SupportTicketController@postTicketFiles');
Route::get('support-ticket/download-file/{id}','SupportTicketController@downloadTicketFile');
Route::get('support-ticket/delete-ticket-file/{id}','SupportTicketController@deleteTicketFile');


/*
|--------------------------------------------------------------------------
| SystemSetting Controller
|--------------------------------------------------------------------------
|
| Discuss in future
|
*/

//======================================================================
// General Setting
//======================================================================
Route::get('settings/general','SettingController@general');
Route::post('settings/post-general-setting','SettingController@postGeneralSetting');
Route::post('settings/post-system-email-setting','SettingController@postSystemEmailSetting');
Route::post('settings/post-system-sms-setting','SettingController@postSystemSMSSetting');
Route::post('settings/post-system-auth-setting','SettingController@postSystemAuthSetting');

//======================================================================
// Localization
//======================================================================
Route::get('settings/localization','SettingController@localization');
Route::post('settings/localization-post','SettingController@localizationPost');



/*Email Template Module*/
Route::get('settings/email-templates','SettingController@emailTemplates');
Route::get('settings/email-template-manage/{id}','SettingController@manageTemplate');
Route::post('settings/email-templates-update','SettingController@updateTemplate');

//======================================================================
// Language Settings
//======================================================================
Route::get('settings/language-settings','SettingController@languageSettings');
Route::post('settings/language-settings/add','SettingController@addLanguage');
Route::get('settings/language-settings-translate/{lid}','SettingController@translateLanguage');
Route::post('settings/language-settings-translate-post','SettingController@translateLanguagePost');
Route::get('settings/language-settings-manage/{lid}','SettingController@languageSettingsManage');
Route::post('settings/language-settings-manage-post','SettingController@languageSettingManagePost');
Route::get('settings/language-settings/delete/{lid}','SettingController@deleteLanguage');

/*Language Change*/
Route::get('language/change/{id}','SettingController@languageChange');

//======================================================================
// Payment Gateway Setting
//======================================================================
Route::get('settings/payment-gateways','SettingController@paymentGateways');
Route::get('settings/payment-gateway-manage/{id}','SettingController@paymentGatewayManage');
Route::post('settings/post-payment-gateway-manage','SettingController@postPaymentGatewayManage');

Route::get('settings/add-payment-gateway','SettingController@addPaymentGateway');
Route::post('settings/post-add-payment-gateway','SettingController@postAddPaymentGateway');

//======================================================================
// Background jobs
//======================================================================
Route::get('settings/background-jobs','SettingController@backgroundJobs');



/*
|--------------------------------------------------------------------------
| SMSController
|--------------------------------------------------------------------------
|
| discuss in future
|
*/

//======================================================================
// Coverage
//======================================================================
Route::get('sms/coverage','SMSController@coverage');
Route::get('sms/manage-coverage/{id}','SMSController@manageCoverage');
Route::post('sms/post-manage-coverage','SMSController@postManageCoverage');
Route::get('sms/add-operator/{id}','SMSController@addOperator');
Route::post('sms/post-add-operator','SMSController@postAddOperator');
Route::get('sms/view-operator/{id}','SMSController@viewOperator');
Route::get('sms/manage-operator/{id}','SMSController@manageOperator');
Route::post('sms/post-manage-operator','SMSController@postManageOperator');
Route::get('sms/delete-operator/{id}','SMSController@deleteOperator');

//======================================================================
// SenderID Management
//======================================================================
Route::get('sms/sender-id-management','SMSController@senderIdManagement');
Route::get('sms/add-sender-id','SMSController@addSenderID');
Route::post('sms/post-new-sender-id','SMSController@postNewSenderID');
Route::get('sms/view-sender-id/{id}','SMSController@viewSenderID');
Route::post('sms/post-update-sender-id','SMSController@postUpdateSenderID');
Route::get('sms/delete-sender-id/{id}','SMSController@deleteSenderID');

//======================================================================
// SMS Price Plan
//======================================================================
Route::get('sms/price-plan','SMSController@pricePlan');
Route::get('sms/add-price-plan','SMSController@addPricePlan');
Route::post('sms/post-new-price-plan','SMSController@postNewPricePlan');
Route::get('sms/manage-price-plan/{id}','SMSController@managePricePlan');
Route::post('sms/post-manage-price-plan','SMSController@postManagePricePlan');
Route::get('sms/add-plan-feature/{id}','SMSController@addPlanFeature');
Route::post('sms/post-new-plan-feature','SMSController@postNewPlanFeature');
Route::get('sms/view-plan-feature/{id}','SMSController@viewPlanFeature');
Route::get('sms/delete-plan-feature/{id}','SMSController@deletePlanFeature');
Route::get('sms/manage-plan-feature/{id}','SMSController@managePlanFeature');
Route::post('sms/post-manage-plan-feature','SMSController@postManagePlanFeature');
Route::get('sms/delete-price-plan/{id}','SMSController@deletePricePlan');

/*Version 1.3*/

//======================================================================
// SMS Price Bundles
//======================================================================
Route::get('sms/price-bundles','SMSController@priceBundles');
Route::post('sms/post-sms-bundles','SMSController@postPriceBundles');



//======================================================================
// SMS Gateway Manage
//======================================================================
Route::get('sms/http-sms-gateway','SMSController@httpSmsGateways');
Route::get('sms/smpp-sms-gateway','SMSController@smppSmsGateways');
Route::any('sms/get-all-gateways-data','SMSController@getAllGatewaysData');
Route::any('sms/get-all-smpp-gateways-data','SMSController@getAllSMPPGatewaysData');
Route::get('sms/add-sms-gateways','SMSController@addSmsGateway');
Route::get('sms/add-smpp-sms-gateways','SMSController@addSMPPSmsGateway');
Route::post('sms/post-new-sms-gateway','SMSController@postNewSmsGateway');
Route::post('sms/post-new-smpp-sms-gateway','SMSController@postNewSMPPGateway');
Route::get('sms/gateway-manage/{id}','SMSController@smsGatewayManage');
Route::get('sms/custom-gateway-manage/{id}','SMSController@customSmsGatewayManage');
Route::post('sms/post-manage-sms-gateway','SMSController@postManageSmsGateway');
Route::post('sms/post-custom-sms-gateway','SMSController@postCustomSmsGateway');
Route::get('sms/delete-sms-gateway/{id}','SMSController@deleteSmsGateway');

//======================================================================
// Version 2.4 (Two way communication)
//======================================================================
Route::get('sms/custom-gateway-two-way/{id}','SMSController@customGatewayTwoWay');
Route::post('sms/post-update-two-way-communication','SMSController@postCustomGatewayTwoWay');
Route::any('sms/receive-message/{id}','PublicAccessController@replyCustomGatewayMessage');

//======================================================================
// Send Quick SMS (Version 2.2)
//======================================================================
Route::get('sms/quick-sms','SMSController@sendQuickSMS');
Route::post('sms/post-quick-sms','SMSController@postQuickSMS');



//======================================================================
// Send Bulk SMS
//======================================================================
Route::get('sms/send-sms','SMSController@sendBulkSMS');
Route::get('sms/get-contact-list-ids','SMSController@getRecipientsData');
Route::post('sms/post-bulk-sms','SMSController@postSendBulkSMS');
Route::post('sms/get-template-info','SMSController@postGetTemplateInfo');

//======================================================================
// Send SMS From File
//======================================================================
Route::get('sms/send-sms-file','SMSController@sendBulkSMSFile');
Route::get('sms/download-sample-sms-file','SMSController@downloadSampleSMSFile');
Route::post('sms/post-sms-from-file','SMSController@postSMSFromFile');

//======================================================================
// Send Schedule SMS
//======================================================================
Route::get('sms/send-schedule-sms','SMSController@sendScheduleSMS');
Route::post('sms/post-schedule-sms','SMSController@postScheduleSMS');
Route::get('sms/send-schedule-sms-file','SMSController@sendScheduleSMSFile');
Route::post('sms/post-schedule-sms-from-file','SMSController@postScheduleSMSFile');
Route::get('sms/update-schedule-sms','SMSController@updateScheduleSMS');
Route::any('sms/get-all-schedule-sms','SMSController@getAllScheduleSMS');
Route::get('sms/manage-update-schedule-sms/{id}','SMSController@manageUpdateScheduleSMS');
Route::post('sms/post-update-schedule-sms','SMSController@postUpdateScheduleSMS');
Route::get('sms/delete-schedule-sms/{id}','SMSController@deleteScheduleSMS');
Route::post('sms/delete-bulk-schedule-sms','SMSController@deleteBulkScheduleSMS');


//======================================================================
// SMS Templates
//======================================================================
Route::get('sms/sms-templates','SMSController@smsTemplates');
Route::get('sms/create-sms-template','SMSController@createSmsTemplate');
Route::post('sms/post-sms-template','SMSController@postSmsTemplate');
Route::get('sms/manage-sms-template/{id}','SMSController@manageSmsTemplate');
Route::post('sms/post-manage-sms-template','SMSController@postManageSmsTemplate');
Route::get('sms/delete-sms-template/{id}','SMSController@deleteSmsTemplate');

//======================================================================
// API Information
//======================================================================
Route::get('sms-api/info','SMSController@apiInfo');
Route::get('sms-api/sdk','SMSController@sdkInfo');
Route::post('sms-api/update-info','SMSController@updateApiInfo');
Route::any('sms/api','PublicAccessController@ultimateSMSApi');

//======================================================================
// Two Way Gateway
//======================================================================
Route::any('sms/reply-twilio','PublicAccessController@replyTwilio');
Route::any('sms/reply-txtlocal','PublicAccessController@replyTxtLocal');
Route::any('sms/reply-smsglobal','PublicAccessController@replySmsGlobal');
Route::any('sms/reply-bulk-sms','PublicAccessController@replyBulkSMS');
Route::any('sms/reply-nexmo','PublicAccessController@replyNexmo');
Route::any('sms/reply-plivo','PublicAccessController@replyPlivo');
Route::any('sms/delivery-report-bulk-sms','PublicAccessController@deliveryReportBulkSMS');
Route::any('sms/delivery-report-infobip','PublicAccessController@deliveryReportInfobip');
Route::any('sms/reply-message-bird','PublicAccessController@replyMessageBird');
Route::any('sms/reply-infobip','PublicAccessController@replyInfoBip');
Route::any('sms/reply-diafaan','PublicAccessController@replyDiafaan');
Route::any('sms/reply-whatsapp','PublicAccessController@replyWhatsApp');
Route::any('sms/reply-easysendsms','PublicAccessController@replyEasySendSMS');
Route::any('sms/reply-gatewayapi','PublicAccessController@replyGatewayAPI');
Route::any('sms/reply-46elks','PublicAccessController@reply46ELKS');
Route::any('sms/reply-signalwire','PublicAccessController@replySignalWire');
Route::any('sms/reply-apiwha','PublicAccessController@replyAPIWHA');
Route::any('sms/reply-flowroute','PublicAccessController@replyFlowRoute');
Route::any('mms/reply-flowroute','PublicAccessController@replyFlowRouteMMS');
Route::any('sms/reply-zang','PublicAccessController@replyZang');
Route::any('sms/reply-smpp','PublicAccessController@replySMPP');
Route::any('sms/reply-thinq','PublicAccessController@replyThinq');
Route::any('sms/reply-voyant','PublicAccessController@replyVoyant');
Route::any('sms/reply-telnyx','PublicAccessController@replyTelnyx');
Route::any('sms/reply-bandwidth','PublicAccessController@replyBandwidth');
Route::any('sms/reply-019SMS','PublicAccessController@reply019SMS');

Route::any('sms/delivery-report-46elks','PublicAccessController@deliveryReport46ELKS');
Route::any('sms/delivery-report-smpp','PublicAccessController@deliveryReportSMPP');
Route::any('sms/delivery-report-africastalking','PublicAccessController@deliveryReportAfricasTalking');

//======================================================================
// SMS History
//======================================================================
Route::get('sms/history','ReportsController@smsHistory');
Route::any('sms/get-sms-history-data/','ReportsController@getSmsHistoryData');
Route::get('sms/view-inbox/{id}','ReportsController@smsViewInbox');
Route::get('sms/post-reply-sms/{id}/{message}','ReportsController@postReplySMS');
Route::get('sms/delete-sms/{id}','ReportsController@deleteSMS');
Route::post('sms/bulk-sms-delete','ReportsController@bulkDeleteSMS');


//======================================================================
// For Client Portal
//======================================================================
/*
|--------------------------------------------------------------------------
| User Controller
|--------------------------------------------------------------------------
|
| Maintain user from client portal
|
*/

//======================================================================
// Client Manage
//======================================================================
Route::get('user/all','UserController@allUsers');
Route::any('user/get-all-clients-data/','UserController@getAllClients');
Route::get('user/add', 'UserController@addUser');
Route::post('user/post-new-user', 'UserController@addUserPost');
Route::get('user/delete-user/{id}', 'UserController@deleteUser');

//======================================================================
// Profile Manage
//======================================================================
Route::get('user/view/{id}', 'UserController@viewUser');
Route::post('user/update-limit', 'UserController@updateLimit');
Route::post('user/update-image', 'UserController@updateImage');
Route::post('user/update-user-post', 'UserController@updateUser');
Route::post('user/send-sms', 'UserController@sendSMS');

/*Export N Import wit CSV*/
Route::get('user/export-n-import', 'UserController@exportImport');
Route::get('user/export-user', 'UserController@exportUsers');
Route::get('user/download-sample-csv', 'UserController@downloadSampleCSV');
Route::post('user/post-new-user-csv', 'UserController@addNewUserCSV');

//======================================================================
// Client Group Manage
//======================================================================
Route::get('users/groups', 'UserController@userGroups');
Route::post('users/add-new-group', 'UserController@addNewUserGroup');
Route::post('users/update-group', 'UserController@updateUserGroup');
Route::get('users/export-user-group/{id}', 'UserController@exportUserGroup');
Route::get('users/delete-group/{id}', 'UserController@deleteUserGroup');


/*
|--------------------------------------------------------------------------
| ClientInvoiceController
|--------------------------------------------------------------------------
|
| Discuss in future
|
*/

Route::get('user/invoices/all', 'ClientInvoiceController@allInvoices');
Route::get('user/invoices/recurring', 'ClientInvoiceController@recurringInvoices');
Route::get('user/invoices/view/{id}', 'ClientInvoiceController@viewInvoice');
Route::get('user/invoices/client-iview/{id}', 'ClientInvoiceController@clientIView');
Route::get('user/invoices/iprint/{id}', 'ClientInvoiceController@printView');
Route::get('user/invoices/download-pdf/{id}', 'ClientInvoiceController@downloadPdf');
Route::get('user/invoices/iprint/{id}', 'ClientInvoiceController@printView');
Route::get('user/invoices/iprint/{id}', 'ClientInvoiceController@printView');
Route::any('user/invoices/pay-invoice', 'PaymentController@payInvoice');
Route::any('user/invoice/success/{token}/{id}', 'PaymentController@successInvoice');
Route::any('user/invoice/notify/{id}', 'PaymentController@notifyInvoice');
Route::any('user/invoice/cancel/{id}', 'PaymentController@cancelledInvoice');
Route::any('user/slydepay/receive-callback', 'PaymentController@slydepayReceiveCallback');
Route::post('user/invoices/pay-with-stripe', 'PaymentController@payWithStripe');

Route::any('cinetpay/cancel', 'PaymentController@cancelCinetpay');
Route::any('cinetpay/success', 'PaymentController@successCinetpay');
Route::any('cinetpay/notify', 'PaymentController@notifyCinetpay');

/*
|--------------------------------------------------------------------------
| UserTicketController
|--------------------------------------------------------------------------
|
|
|
*/

Route::get('user/tickets/all','UserTicketController@allSupportTickets');
Route::get('user/tickets/create-new','UserTicketController@createNewTicket');
Route::post('user/tickets/post-ticket','UserTicketController@postTicket');
Route::get('user/tickets/view-ticket/{id}','UserTicketController@viewTicket');
Route::post('user/tickets/replay-ticket','UserTicketController@replayTicket');
Route::post('user/tickets/post-ticket-files','UserTicketController@postTicketFiles');
Route::get('user/tickets/download-file/{id}','UserTicketController@downloadTicketFile');


/*
|--------------------------------------------------------------------------
| UserSMSController
|--------------------------------------------------------------------------
|
|
|
*/

//======================================================================
// Sender ID Management
//======================================================================
Route::get('user/sms/sender-id-management','UserSMSController@senderIdManagement');
Route::post('user/sms/post-sender-id','UserSMSController@postSenderID');

//======================================================================
// Send Quick SMS (Version 2.2)
//======================================================================
Route::get('user/sms/quick-sms','UserSMSController@sendQuickSMS');
Route::post('user/sms/post-quick-sms','UserSMSController@postQuickSMS');

//======================================================================
// Get SMS Template (Version 1.3)
//======================================================================
Route::post('user/sms/get-template-info','UserSMSController@postGetTemplateInfo');

//======================================================================
// Send SMS
//======================================================================
Route::get('user/sms/send-sms','UserSMSController@sendBulkSMS');
Route::post('user/sms/post-bulk-sms','UserSMSController@postSendBulkSMS');
Route::get('user/sms/get-contact-list-ids','UserSMSController@getRecipientsData');

//======================================================================
// Send SMS From File
//======================================================================
Route::get('user/sms/send-sms-file','UserSMSController@sendSMSFromFile');
Route::get('user/sms/download-sample-sms-file','UserSMSController@downloadSampleSMSFile');
Route::post('user/sms/post-sms-from-file','UserSMSController@postSMSFromFile');


//======================================================================
// Send Schedule SMS
//======================================================================
Route::get('user/sms/send-schedule-sms','UserSMSController@sendScheduleSMS');
Route::post('user/sms/post-schedule-sms','UserSMSController@postScheduleSMS');
Route::get('user/sms/send-schedule-sms-file','UserSMSController@sendScheduleSMSFromFile');
Route::post('user/sms/post-schedule-sms-from-file','UserSMSController@postScheduleSMSFromFile');

/*Version 1.1*/
Route::get('user/sms/update-schedule-sms','UserSMSController@updateScheduleSMS');
Route::any('user/sms/get-all-schedule-sms','UserSMSController@getAllScheduleSMS');
Route::get('user/sms/manage-update-schedule-sms/{id}','UserSMSController@manageUpdateScheduleSMS');
Route::post('user/sms/post-update-schedule-sms','UserSMSController@postUpdateScheduleSMS');
Route::get('user/sms/delete-schedule-sms/{id}','UserSMSController@deleteScheduleSMS');
Route::post('user/sms/delete-bulk-schedule-sms','UserSMSController@deleteBulkScheduleSMS');


//======================================================================
// SMS History
//======================================================================
Route::get('user/sms/history','UserSMSController@smsHistory');
Route::get('user/sms/get-sms-history-data','UserSMSController@getSmsHistoryData');
Route::get('user/sms/view-inbox/{id}','UserSMSController@smsViewInbox');
Route::get('user/sms/post-reply-sms/{id}/{message}','UserSMSController@postReplySMS');
Route::post('user/sms/bulk-sms-delete/','UserSMSController@deleteBulkSMS');
Route::get('user/sms/delete-sms/{id}','UserSMSController@deleteSMS');

//======================================================================
// Purchase SMS Plan
//======================================================================
Route::get('user/sms/purchase-sms-plan','UserSMSController@purchaseSMSPlan');
Route::get('user/sms/sms-plan-feature/{id}','UserSMSController@smsPlanFeature');
Route::post('user/sms/post-purchase-sms-plan','PaymentController@purchaseSMSPlanPost');
Route::any('user/sms/purchase-plan/success/{token}/{id}','PaymentController@successPurchase');
Route::any('user/sms/purchase-plan/notify/{client_id}/{id}','PaymentController@notifyPurchase');
Route::any('user/sms/purchase-plan/cancel/{id}','PaymentController@cancelledPurchase');
Route::post('user/sms/purchase-with-stripe','PaymentController@purchaseWithStripe');

Route::get('user/hosting/purchase-vps-plan','UserSMSController@purchaseVPSPlan');

Route::get('user/hosting/purchase-vpn-plan','UserSMSController@purchaseVPNPlan');

Route::get('user/hosting/purchase-proxies-plan','UserSMSController@purchaseProxiesPlan');


/*Version 1.3*/
Route::get('user/sms/buy-unit','UserSMSController@buyUnit');
Route::post('user/get-transaction','UserSMSController@getTransaction');
Route::post('users/post-buy-unit','PaymentController@postBuyUnit');
Route::any('user/paystack/callback','PaymentController@payStackCallback');
Route::any('user/sms/buy-unit/success/{token}/{id}','PaymentController@buyUnitSuccess');
Route::any('user/sms/buy-unit/notify/{client_id}/{id}','PaymentController@buyUnitNotify');
Route::any('user/sms/buy-unit/cancel','PaymentController@buyUnitCancel');
Route::post('user/sms/buy-unit-with-stripe','PaymentController@buyUnitWithStripe');

//======================================================================
// API Information
//======================================================================
Route::get('user/sms-api/info','UserSMSController@apiInfo');
Route::get('user/sms-api/sdk','UserSMSController@sdkInfo');
Route::post('user/sms-api/update-info','UserSMSController@updateApiInfo');

//======================================================================
// User Information
//======================================================================
Route::get('user/edit-profile','ClientDashboardController@editProfile');
Route::post('user/post-personal-info', 'ClientDashboardController@postPersonalInfo');
Route::post('user/update-avatar', 'ClientDashboardController@updateAvatar');
Route::get('user/change-password', 'ClientDashboardController@changePassword');
Route::post('user/update-password', 'ClientDashboardController@updatePassword');
Route::get('user/language/change/{id}', 'ClientDashboardController@changeLanguage');

//======================================================================
// SMS Templates
//======================================================================
Route::get('user/sms/sms-templates','UserSMSController@smsTemplates');
Route::get('user/sms/create-sms-template','UserSMSController@createSmsTemplate');
Route::post('user/sms/post-sms-template','UserSMSController@postSmsTemplate');
Route::get('user/sms/manage-sms-template/{id}','UserSMSController@manageSmsTemplate');
Route::post('user/sms/post-manage-sms-template','UserSMSController@postManageSmsTemplate');
Route::get('user/sms/delete-sms-template/{id}','UserSMSController@deleteSmsTemplate');


/*
|--------------------------------------------------------------------------
| Start Version 2.0 Work from here
|--------------------------------------------------------------------------
|
| Contact Module for version 2.0
|
*/

//======================================================================
// Contact Module
//======================================================================
Route::get('sms/phone-book','ContactController@phoneBook');
Route::post('sms/post-phone-book','ContactController@postPhoneBook');
Route::get('sms/add-contact/{id}','ContactController@addContact');
Route::post('sms/post-new-contact','ContactController@postNewContact');
Route::post('sms/update-single-contact','ContactController@postSingleContact');
Route::post('sms/update-phone-book','ContactController@updatePhoneBook');
Route::get('sms/view-contact/{id}','ContactController@viewContact');
Route::get('sms/edit-contact/{id}','ContactController@editContact');
Route::any('sms/get-all-contact/{id}','ContactController@getAllContact');
Route::get('sms/delete-contact/{id}','ContactController@deleteContact');
Route::post('sms/delete-bulk-contact','ContactController@deleteBulkContact');
Route::get('sms/import-contacts','ContactController@importContacts');
Route::get('sms/download-contact-sample-file','ContactController@downloadContactSampleFile');
Route::post('sms/post-import-file-contact','ContactController@postImportContact');
Route::post('sms/post-multiple-contact','ContactController@postMultipleContact');
Route::post('sms/get-recipients','ContactController@getRecipients');
Route::get('sms/delete-import-phone-number/{id}','ContactController@deleteImportPhoneNumber');

//======================================================================
// User Contact Module
//======================================================================
Route::get('user/phone-book','UserContactController@phoneBook');
Route::post('user/post-phone-book','UserContactController@postPhoneBook');
Route::post('user/update-phone-book','UserContactController@updatePhoneBook');
Route::get('user/add-contact/{id}','UserContactController@addContact');
Route::get('user/view-contact/{id}','UserContactController@viewContact');
Route::get('user/edit-contact/{id}','UserContactController@editContact');
Route::post('user/update-single-contact','UserContactController@postSingleContact');
Route::any('user/get-all-contact/{id}','UserContactController@getAllContact');
Route::get('user/delete-contact/{id}','UserContactController@deleteContact');
Route::post('user/sms/delete-bulk-contact','UserContactController@deleteBulkContact');
Route::get('user/sms/import-contacts','UserContactController@importContacts');
Route::get('user/sms/download-contact-sample-file','UserContactController@downloadContactSampleFile');
Route::post('user/post-import-file-contact','UserContactController@postImportContact');
Route::post('user/post-multiple-contact','UserContactController@postMultipleContact');
Route::post('user/post-new-contact','UserContactController@postNewContact');
Route::post('user/update-single-contact','UserContactController@postSingleContact');
Route::post('user/sms/get-recipients','UserSMSController@getRecipients');
Route::get('user/sms/delete-import-phone-number/{id}','UserContactController@deleteImportPhoneNumber');


//======================================================================
// BlackList Contacts Module For Admin
//======================================================================
Route::get('sms/blacklist-contacts','SMSController@blacklistContacts');
Route::any('sms/get-blacklist-contact','SMSController@getBlacklistContacts');
Route::post('sms/post-blacklist-contact','SMSController@postBlacklistContact');
Route::get('sms/delete-blacklist-contact/{id}','SMSController@deleteBlacklistContact');
Route::post('sms/delete-bulk-blacklist-contact','SMSController@deleteBulkBlacklistContact');
Route::get('sms/add-to-blacklist/{id}','ContactController@addToBlacklist');

//======================================================================
// BlackList Contacts Module For User
//======================================================================
Route::get('user/sms/blacklist-contacts','UserSMSController@blacklistContacts');
Route::post('user/sms/post-blacklist-contact','UserSMSController@postBlacklistContact');
Route::get('user/sms/delete-blacklist-contact/{id}','UserSMSController@deleteBlacklistContact');
Route::any('user/sms/get-blacklist-contact','UserSMSController@getBlacklistContacts');
Route::post('user/sms/delete-bulk-blacklist-contact','UserSMSController@deleteBulkBlacklistContact');
Route::get('user/sms/add-to-blacklist/{id}','UserContactController@addToBlacklist');

//======================================================================
// Dynamic file upload
//======================================================================
Route::post('sms/get-csv-file-info','CommonDataController@getCsvFileInfo');
Route::post('client/get-csv-file-info','CommonDataController@getClientCsvFileInfo');


//======================================================================
// Paynow Payment Gateway
//======================================================================
Route::any('user/invoice/paynow/{id}', 'PaymentController@payNowInvoice');
Route::any('user/sms/purchase-plan/paynow/{id}','PaymentController@payNowPurchasePlan');
Route::any('user/sms/buy-unit/paynow/{id}','PaymentController@buyUnitByPayNow');
Route::any('user/keywords/buy-keyword/paynow/{id}','PaymentController@buyKeywordByPayNow');

//======================================================================
// Purchase code
//======================================================================
Route::get('settings/purchase-code','SettingController@purchaseCode');
Route::post('settings/update-purchase-key','SettingController@updatePurchaseCode');



//======================================================================
// Filter Spam or Fraud word
//======================================================================
Route::get('sms/spam-words','ContactController@spamWords');
Route::any('sms/get-spam-words','ContactController@getSpamWords');
Route::post('sms/post-spam-word','ContactController@postSpamWord');
Route::get('sms/delete-spam-word/{id}','ContactController@deleteSpamWord');


//======================================================================
// Verify Block Message
//======================================================================
Route::get('sms/block-message','ReportsController@blockMessage');
Route::any('sms/get-block-message-data','ReportsController@getBlockMessageData');
Route::get('sms/view-block-message/{id}','ReportsController@viewBlockMessage');
Route::get('sms/release-block-message/{id}','ReportsController@releaseBlockMessage');
Route::get('sms/delete-block-message/{id}','ReportsController@deleteBlockMessage');


//======================================================================
// Recurring SMS
//======================================================================
Route::get('sms/recurring-sms','SMSController@recurringSMS');
Route::any('sms/get-recurring-sms-data','SMSController@getRecurringSMSData');
Route::post('sms/bulk-recurring-sms-delete','SMSController@bulkDeleteRecurringSMS');
Route::post('sms/bulk-recurring-sms-contact-delete','SMSController@bulkDeleteRecurringSMSContact');
Route::get('sms/delete-recurring-sms/{id}','SMSController@deleteRecurringSMS');
Route::get('sms/delete-recurring-sms-contact/{id}','SMSController@deleteRecurringSMSContact');

Route::get('sms/send-recurring-sms','SMSController@sendRecurringSMS');
Route::post('sms/post-recurring-sms','SMSController@postRecurringSMS');

Route::get('sms/stop-recurring-sms/{id}','SMSController@stopRecurringSMS');
Route::get('sms/start-recurring-sms/{id}','SMSController@startRecurringSMS');
Route::get('sms/update-recurring-sms/{id}','SMSController@updateRecurringSMS');
Route::get('sms/add-recurring-sms-contact/{id}','SMSController@addRecurringSMSContact');
Route::post('sms/post-recurring-sms-contact','SMSController@postRecurringSMSContact');
Route::get('sms/update-recurring-sms-contact/{id}','SMSController@updateRecurringSMSContact');
Route::get('sms/update-recurring-sms-contact-data/{id}','SMSController@updateRecurringSMSContactData');
Route::any('sms/get-recurring-sms-contact-data/{id}','SMSController@getRecurringSMSContactData');
Route::post('sms/post-update-recurring-sms','SMSController@postUpdateRecurringSMS');
Route::post('sms/post-update-recurring-sms-contact-data','SMSController@postUpdateRecurringSMSContactData');
Route::get('sms/send-recurring-sms-file','SMSController@sendRecurringSMSFile');
Route::post('sms/post-recurring-sms-file','SMSController@postRecurringSMSFile');



//======================================================================
//User Recurring SMS
//======================================================================

Route::get('user/sms/recurring-sms','UserSMSController@recurringSMS');
Route::any('user/sms/get-recurring-sms-data','UserSMSController@getRecurringSMSData');
Route::post('user/sms/bulk-recurring-sms-delete','UserSMSController@bulkDeleteRecurringSMS');
Route::post('user/sms/bulk-recurring-sms-contact-delete','UserSMSController@bulkDeleteRecurringSMSContact');
Route::get('user/sms/delete-recurring-sms/{id}','UserSMSController@deleteRecurringSMS');
Route::get('user/sms/delete-recurring-sms-contact/{id}','UserSMSController@deleteRecurringSMSContact');

Route::get('user/sms/send-recurring-sms','UserSMSController@sendRecurringSMS');
Route::post('user/sms/post-recurring-sms','UserSMSController@postRecurringSMS');

Route::get('user/sms/stop-recurring-sms/{id}','UserSMSController@stopRecurringSMS');
Route::get('user/sms/start-recurring-sms/{id}','UserSMSController@startRecurringSMS');
Route::get('user/sms/update-recurring-sms/{id}','UserSMSController@updateRecurringSMS');
Route::get('user/sms/add-recurring-sms-contact/{id}','UserSMSController@addRecurringSMSContact');
Route::post('user/sms/post-recurring-sms-contact','UserSMSController@postRecurringSMSContact');
Route::get('user/sms/update-recurring-sms-contact/{id}','UserSMSController@updateRecurringSMSContact');
Route::get('user/sms/update-recurring-sms-contact-data/{id}','UserSMSController@updateRecurringSMSContactData');
Route::any('user/sms/get-recurring-sms-contact-data/{id}','UserSMSController@getRecurringSMSContactData');
Route::post('user/sms/post-update-recurring-sms','UserSMSController@postUpdateRecurringSMS');
Route::post('user/sms/post-update-recurring-sms-contact-data','UserSMSController@postUpdateRecurringSMSContactData');
Route::get('user/sms/send-recurring-sms-file','UserSMSController@sendRecurringSMSFile');
Route::post('user/sms/post-recurring-sms-file','UserSMSController@postRecurringSMSFile');

//======================================================================
// Webxpay Payment gateway
//======================================================================
Route::any('user/webxpay/receive-callback', 'PaymentController@webxpayReceiveCallback');

//======================================================================
// Subscription api for wordpress plugin
//======================================================================
Route::any('contacts/api','PublicAccessController@ultimateSMSContactApi');



//======================================================================
// Version 2.4.0
//======================================================================

//======================================================================
// Keyword Settings
//======================================================================
Route::get('keywords/settings','SMSController@keywordSettings');
Route::post('keywords/post-keyword-setting','SMSController@postKeywordSettings');

Route::get('keywords/add','SMSController@addKeyword');
Route::post('keywords/post-new-keyword','SMSController@postNewKeyword');
Route::get('keywords/all','SMSController@allKeywords');
Route::any('keywords/get-keywords','SMSController@getKeywordsData');
Route::get('keywords/view/{id}','SMSController@viewKeyword');
Route::post('keywords/post-manage-keyword','SMSController@postManageKeyword');
Route::get('keywords/remove-mms-file/{id}','SMSController@removeKeywordMMSFile');
Route::get('keywords/delete-keyword/{id}','SMSController@deleteKeyword');
Route::get('sms/campaign-reports','SMSController@campaignReports');
Route::any('sms/get-campaign-history','SMSController@getCampaignReports');
Route::get('sms/manage-campaign/{id}','SMSController@manageCampaign');
Route::any('sms/get-campaign-recipients/{id}','SMSController@getCampaignRecipients');
Route::post('sms/post-update-campaign','SMSController@postUpdateCampaign');
Route::post('sms/bulk-campaign-recipients-delete','SMSController@deleteBulkCampaignRecipients');
Route::get('sms/delete-campaign-recipient/{id}','SMSController@deleteCampaignRecipient');
Route::post('sms/bulk-campaign-delete','SMSController@deleteBulkCampaign');
Route::get('sms/delete-campaign/{id}','SMSController@deleteCampaign');


Route::get('user/keywords','UserSMSController@allKeywords');
Route::any('user/keywords/get-keywords','UserSMSController@getAllKeywords');
Route::get('user/keywords/purchase/{id}','UserSMSController@purchaseKeyword');
Route::post('users/keywords/post-purchase-keyword','PaymentController@postPurchaseKeyword');
Route::any('user/keywords/buy-keyword/success/{token}/{id}','PaymentController@buyKeywordSuccess');
Route::any('user/keywords/buy-keyword/notify/{id}','PaymentController@buyKeywordNotify');
Route::any('user/keywords/buy-keyword/cancel','PaymentController@buyKeywordCancel');
Route::post('user/keywords/buy-keyword-with-stripe','PaymentController@buyKeywordWithStripe');


Route::get('user/keywords/view/{id}','UserSMSController@viewKeyword');
Route::post('user/keywords/post-manage-keyword','UserSMSController@postManageKeyword');
Route::get('user/keywords/remove-mms-file/{id}','UserSMSController@removeKeywordMMSFile');


Route::get('user/sms/campaign-reports','UserSMSController@campaignReports');
Route::any('user/sms/get-campaign-history','UserSMSController@getCampaignReports');
Route::get('user/sms/manage-campaign/{id}','UserSMSController@manageCampaign');
Route::any('user/sms/get-campaign-recipients/{id}','UserSMSController@getCampaignRecipients');
Route::post('user/sms/post-update-campaign','UserSMSController@postUpdateCampaign');
Route::post('user/sms/bulk-campaign-recipients-delete','UserSMSController@deleteBulkCampaignRecipients');
Route::get('user/sms/delete-campaign-recipient/{id}','UserSMSController@deleteCampaignRecipient');
Route::post('user/sms/bulk-campaign-delete','UserSMSController@deleteBulkCampaign');
Route::get('user/sms/delete-campaign/{id}','UserSMSController@deleteCampaign');

/*Coverage*/
Route::get('user/coverage','UserSMSController@getCoverage');
Route::get('user/account','UserSMSController@getAccount');
Route::get('user/domain','UserSMSController@getDomains');
Route::get('user/payments','UserSMSController@payments');



Route::get('user/sms/view-operator/{id}','UserSMSController@viewOperator');

//======================================================================
// Coverage api for wordpress plugin
//======================================================================
Route::any('coverage/api','PublicAccessController@ultimateSMSCoverageApi');
Route::any('coverage/get-operator-price/{country}','PublicAccessController@UltimateSMSOperatorPrice');



//======================================================================
// Chat box
//======================================================================
Route::get('sms/chat-box','ReportsController@chatBox');
Route::post('sms/view-reports','ReportsController@viewChatReports');
Route::post('sms/reply-chat-sms','ReportsController@replyChatSMS');
Route::post('sms/add-to-blacklist','ReportsController@addToBlacklist');
Route::post('sms/remove-chat-history','ReportsController@removeChatHistory');

Route::get('user/sms/chat-box','UserSMSController@chatBox');
Route::post('user/sms/view-reports','UserSMSController@viewChatReports');
Route::post('user/sms/reply-chat-sms','UserSMSController@replyChatSMS');
Route::post('user/sms/add-to-blacklist','UserSMSController@addToBlacklist');
Route::post('user/sms/remove-chat-history','UserSMSController@removeChatHistory');


//Moka Payment

//For Invoice
Route::post('user/pay-invoice-moka', 'PaymentController@payInvoiceMoka');
Route::any('user/pay-with-moka/invoice/{token}/{id}', 'PaymentController@payInvoiceWithMoka');

//For SMS Plan
Route::post('user/pay-sms-plan-moka', 'PaymentController@paySMSPlanMoka');
Route::any('user/pay-with-moka/sms-plan/{token}/{id}', 'PaymentController@paySMSPlanWithMoka');


//For SMS Unit
Route::post('user/pay-sms-unit-moka', 'PaymentController@paySMSUnitMoka');
Route::any('user/pay-with-moka/sms-unit/{token}/{id}', 'PaymentController@paySMSUnitWithMoka');


//For Purchase keyword
Route::post('user/purchase-keyword-moka', 'PaymentController@purchaseKewyordMoka');
Route::any('user/pay-with-moka/purchase-keyword/{token}/{id}', 'PaymentController@purchaseKeywordWithMoka');
