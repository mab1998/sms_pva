<?php

    require('elibomMocks.php');

    class ElibomClientTest extends PHPUnit_Framework_TestCase
    {

        public function testSendMessage()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/messages", 
                                        "method" => "POST", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "{\"destinations\":\"3001111111\",\"text\":\"testing\"}"
                                    );


            $expectedResponse = "{\"deliveryToken\":\"token123123\"}";
            $elibom->stubRequest($expectedRequest, $expectedResponse);


            $deliveryToken = $elibom->sendMessage("3001111111","testing");

            // Assert
            $this->assertEquals("token123123",$deliveryToken);
        }

        public function testSendMessageWithCampaign()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/messages", 
                                        "method" => "POST", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "{\"destinations\":\"3001111111\",\"text\":\"testing\",\"campaign\":\"campaign-test\"}"
                                    );


            $expectedResponse = "{\"deliveryToken\":\"token123123\"}";
            $elibom->stubRequest($expectedRequest, $expectedResponse);


            $deliveryToken = $elibom->sendMessage("3001111111", "testing", "campaign-test");

            // Assert
            $this->assertEquals("token123123",$deliveryToken);
        }

        public function testShowDelivery()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/messages/12312412412321", 
                                        "method" => "GET", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "\"{}\""
                                    );


            $expectedResponse = "{\"data\": {\"user\":\"user@elibom\"}}";
            $elibom->stubRequest($expectedRequest, $expectedResponse);


            $delivery = $elibom->getDelivery("12312412412321");
            $this->assertEquals("user@elibom", $delivery->data->user);
        }

        public function testScheduleMessage()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/messages", 
                                        "method" => "POST", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "{\"destinations\":\"3001111111\",\"text\":\"Test PHP\",\"scheduleDate\":\"12\/12\/2085 08:30\"}"
                                    );


            $expectedResponse = "{\"scheduleId\": \"777\"}";
            $elibom->stubRequest($expectedRequest, $expectedResponse);


            $scheduleId = $elibom->scheduleMessage('3001111111', 'Test PHP', '12/12/2085 08:30');
            $this->assertEquals("777", $scheduleId);
        }

        public function testScheduleMessageWithCampaign()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/messages", 
                                        "method" => "POST", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "{\"destinations\":\"3001111111\",\"text\":\"Test PHP\",\"scheduleDate\":\"12\/12\/2085 08:30\",\"campaign\":\"campaign-test\"}"
                                    );


            $expectedResponse = "{\"scheduleId\": \"777\"}";
            $elibom->stubRequest($expectedRequest, $expectedResponse);


            $scheduleId = $elibom->scheduleMessage('3001111111', 'Test PHP', '12/12/2085 08:30', 'campaign-test');
            $this->assertEquals("777", $scheduleId);
        }

        public function testShowScheduledMessage()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/schedules/777", 
                                        "method" => "GET", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "\"{}\""
                                    );


            $expectedResponse = "{\"messageId\": \"777\"}";
            $elibom->stubRequest($expectedRequest, $expectedResponse);


            $schedules = $elibom->getScheduledMessage('777');
            $this->assertEquals("777", $schedules->messageId);
        }

        public function testShowScheduledMessages()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/schedules/scheduled", 
                                        "method" => "GET", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "\"{}\""
                                    );


            $expectedResponse = "[{\"messageId\": \"777\"}]";
            $elibom->stubRequest($expectedRequest, $expectedResponse);


            $schedules = $elibom->getScheduledMessages();
            $this->assertEquals("777", $schedules[0]->messageId);
        }

        public function testCancelSchedule()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/schedules/777", 
                                        "method" => "DELETE", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "\"{}\""
                                    );


            $expectedResponse = "{}";
            $elibom->stubRequest($expectedRequest, $expectedResponse);
            $elibom->unscheduleMessage('777');
        }

        public function testListUsers()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/users", 
                                        "method" => "GET", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "\"{}\""
                                    );


            $expectedResponse = "[{\"name\":\"carlos\"}]";
            $elibom->stubRequest($expectedRequest, $expectedResponse);
            $users = $elibom->getUsers();

            $this->assertEquals("carlos", $users[0]->name);
        }

        public function testGetUser()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/users/777", 
                                        "method" => "GET", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "\"{}\""
                                    );


            $expectedResponse = "{\"name\":\"carlos\"}";
            $elibom->stubRequest($expectedRequest, $expectedResponse);
            $user = $elibom->getUser('777');

            $this->assertEquals("carlos", $user->name);
        }

        public function testGetAccount()
        {
            $elibom = new MockElibomClient("user@elibom.com","password123");

            $expectedRequest = array(
                                        "url" => "https://www.elibom.com/account", 
                                        "method" => "GET", 
                                        "headers" => array(
                                                            "Authorization" => "Basic dXNlckBlbGlib20uY29tOnBhc3N3b3JkMTIz",
                                                            "X-API-Source" => "php-1.1"
                                                          ),
                                        "body" => "\"{}\""
                                    );


            $expectedResponse = "{\"name\":\"account-name\"}";
            $elibom->stubRequest($expectedRequest, $expectedResponse);
            $account = $elibom->getAccount();

            $this->assertEquals("account-name", $account->name);
        }
    }
?>