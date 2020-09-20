<?php

    namespace Elibom\APIClient\Resources;

    class UserResource extends Resource{

        public function getAll() {
            $response = $this->apiClient->get('users');

            return $response;
        }

        public function get($id) {
            $response = $this->apiClient->get('users/' . $id);

            return $response;
        }
    }
?>