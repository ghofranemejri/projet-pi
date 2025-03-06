<?php

namespace App\Service;

class SmsService
{
    private $accountSid;
    private $authToken;
    private $fromNumber;
    private $toNumber;

    public function __construct()
    {
        $this->accountSid = 'AC5610863e39d9255ae827c9d34a9e7ecd';
        $this->authToken = 'a76effa91656c015de3b7f7526fd31c8';
        $this->fromNumber = '+17752567504';
        $this->toNumber = '+18777804236'; // Using the number from your working example
    }

    public function sendConsultationCancelledSms(string $patientName, \DateTimeInterface $date): bool
    {
        try {
            $message = sprintf(
                'La consultation pour le patient %s prévue le %s est annulée.',
                $patientName,
                $date->format('d/m/Y H:i')
            );

            // Build the URL-encoded form data exactly like the working curl example
            $fields = array(
                'To' => $this->toNumber,
                'MessagingServiceSid' => 'MGa775dad4f657762e3a4404a3633801e1',
                'Body' => $message
            );
            
            $fieldsString = '';
            foreach($fields as $key => $value) {
                $fieldsString .= $key.'='.urlencode($value).'&';
            }
            rtrim($fieldsString, '&');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json");
            curl_setopt($ch, CURLOPT_USERPWD, $this->accountSid . ':' . $this->authToken);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                error_log('Curl error: ' . curl_error($ch));
                error_log('Curl response: ' . $response);
                return false;
            }
            
            curl_close($ch);

            $responseData = json_decode($response, true);
            error_log('Twilio response: ' . print_r($responseData, true));

            if ($httpCode >= 200 && $httpCode < 300) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            error_log('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }
}
