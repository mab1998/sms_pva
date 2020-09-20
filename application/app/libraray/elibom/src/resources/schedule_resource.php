<?php

    namespace Elibom\APIClient\Resources;

    class ScheduleResource extends Resource{

        public function schedule($to, $txt, $date, $campaign = null) {
            $data = array("destinations" => $to, "text" => $txt, "scheduleDate" => $date);
            if (isset($campaign)) {
                $data['campaign'] = $campaign;
            }
            $response = $this->apiClient->post('messages', $data);

            return $response->scheduleId;
        }

        public function get($id) {
            $response = $this->apiClient->get('schedules/' . $id);
            return $response;
        }

        public function getAll() {
            $response = $this->apiClient->get('schedules/scheduled');
            return $response;
        }

        public function unschedule($id) {
            $this->apiClient->delete('schedules/' . $id);
        }
    }
?>