<?php

    namespace Elibom\APIClient;

    require('api_client.php');
    require('resources/resource.php');
    require('resources/message_resource.php');
    require('resources/schedule_resource.php');
    require('resources/delivery_resource.php');
    require('resources/user_resource.php');
    require('resources/account_resource.php');

    use Elibom\APIClient\Resources\MessageResource as MessageResource;
    use Elibom\APIClient\Resources\DeliveryResource as DeliveryResource;
    use Elibom\APIClient\Resources\ScheduleResource as ScheduleResource;
    use Elibom\APIClient\Resources\UserResource as UserResource;
    use Elibom\APIClient\Resources\AccountResource as AccountResource;

    class ElibomClient {

        protected $apiClient;

        public function __construct($u, $t) {
            $this->apiClient = new APIClient($u, $t);
        }

        public function sendMessage($to, $txt, $campaign = null) {
            $message = new MessageResource($this->apiClient);
            $deliveryToken = $message->send($to, $txt, $campaign);

            return $deliveryToken;
        }

        public function getDelivery($deliveryToken) {
            $delivery = new DeliveryResource($this->apiClient);
            $deliveryData = $delivery->get($deliveryToken);

            return $deliveryData;
        }

        public function scheduleMessage($to, $txt, $date, $campaign = null) {
            $scheduleResource = new ScheduleResource($this->apiClient);
            $scheduleId = $scheduleResource->schedule($to, $txt, $date, $campaign);

            return $scheduleId;
        }

        public function getScheduledMessage($scheduleId) {
            $scheduleResource = new ScheduleResource($this->apiClient);
            $schedule = $scheduleResource->get($scheduleId);

            return $schedule;
        }

        public function getScheduledMessages() {
            $scheduleResource = new ScheduleResource($this->apiClient);
            $schedules = $scheduleResource->getAll();

            return $schedules;
        }

        public function unscheduleMessage($scheduleId) {
            $scheduleResource = new ScheduleResource($this->apiClient);
            $schedules = $scheduleResource->unschedule($scheduleId);
        }

        public function getUsers() {
            $userResource = new UserResource($this->apiClient);
            $users = $userResource->getAll();

            return $users;
        }

        public function getUser($userId) {
            $userResource = new UserResource($this->apiClient);
            $user = $userResource->get($userId);

            return $user;
        }

        public function getAccount() {
            $accountResource= new AccountResource($this->apiClient);
            $account = $accountResource->get();

            return $account;
        }
    }

?>
