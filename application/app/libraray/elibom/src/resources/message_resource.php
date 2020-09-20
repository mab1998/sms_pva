<?php

    namespace Elibom\APIClient\Resources;

    class MessageResource extends Resource {

        public function send($to, $txt, $campaign = null) {
            $data = array("destinations" => $to, "text" => $txt);
            if (isset($campaign)) {
                $data['campaign'] = $campaign;
            }

            $response = $this->apiClient->post('messages', $data);
            return $response->deliveryToken;
        }
    }
?>