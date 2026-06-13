<?php

if (! function_exists('sendSmsToPatient')) {
    function sendSmsToPatient($phone, $message) {
        $user = env('SMS_USER');
        $password = env('SMS_PASSWORD');
        $senderId = env('SMS_SENDER_ID');
        $templateId = env('SMS_TEMPLATE_ID');
        $peid = env('SMS_PEID');
        $url = 'http://bulksms.saakshisoftware.com/api/mt/SendSMS';

        $params = [
            'user' => $user,
            'password' => $password,
            'senderid' => $senderId,
            'channel' => 'Trans',
            'DCS' => 0,
            'flashsms' => 0,
            'number' => $phone,
            'text' => $message,
            'route' => '4',
            'DLTTemplateId' => $templateId,
            'PEID' => $peid,
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url, [
                'query' => $params,
                'timeout' => 10,
            ]);

            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            return false;
        }
    }
}
