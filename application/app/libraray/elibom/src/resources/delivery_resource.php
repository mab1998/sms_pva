<?php

    namespace Elibom\APIClient\Resources;

    class DeliveryResource extends Resource{

        public function get($id) {
            $response = $this->apiClient->get('messages/' . $id);
            return $response;
        }
    }
?>