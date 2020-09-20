<?php

    namespace Elibom\APIClient\Resources;

    class AccountResource extends Resource{

        public function get() {
            $response = $this->apiClient->get('account');
            return $response;
        }
    }
?>