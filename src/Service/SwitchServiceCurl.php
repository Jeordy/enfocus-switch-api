<?php

namespace App\Service;

/**
 * @class SwitchServiceCurl
 * Example class using curl_exec
 */
class SwitchServiceCurl
{
    private const SSL_KEY = __DIR__.'/../Key/public.key';

    // Enfocus Switch ip address with port number
    private const SERVER_IP = 'http://127.0.0.1:51088';

    private const LOGIN = '/login';

    private const SUBMIT_POINTS = '/api/v1/submitpoints';

    private const JOB_SUBMIT = '/api/v1/job';

    private const TEST_FILE = __DIR__.'/../Files/On_Page_SEO_Checklist_Backlinko.pdf';

    /**
     * Generate encrypted password
     *
     * @param string $password
     * @return string|null
     */
    private function generateEncryptedPassword($password): ?string
    {
        // Read the public key
        $pub  = file_get_contents(self::SSL_KEY);
        $key = openssl_get_publickey($pub);

        // Use openssl_public_encrypt for encrypting the password. Then use the $encrypted variable that holds the encoded password
        $ssl = openssl_public_encrypt($password , $encrypted , $key);
        // User password encrypted by the RSA algorithm with padding PKCS1, then converted to base64 and preceded by '!@$'
        return '!@$' . base64_encode($encrypted);
    }

    /**
     * Login to Enfocus Switch
     *
     * @param string $username
     * @param string $password
     * @return string|null
     */
    public function login($username, $password): ?string
    {
        $jsonBody = json_encode([
            'username' => $username,
            'password' => $this->generateEncryptedPassword($password)
        ]);

        // create a new curl resource
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, self::SERVER_IP . self::LOGIN);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        // Add the json body
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody); 

        // Execute the curl resource
        $result = curl_exec($ch);

        // close curl resource, and free up system resources
        curl_close($ch);

        return $result;
    }

    /**
     * List all Submitpoints
     *
     * @param string $token
     * @return string|null
     */
    public function listSubmitPoints($token): ?string
    {
        // create a new curl resource
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, self::SERVER_IP . self::SUBMIT_POINTS);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        
        // Execute the curl resource
        $result = curl_exec($ch);

        // close curl resource, and free up system resources
        curl_close($ch);

        return $result;
    }

    /**
     * Submit file to Switch
     *
     * @param string $token
     * @param string $submitPoint
     * @return string|null
     */
    public function jobSubmit($token, $submitPoint): ?string
    {
        $submitPoint = json_decode($submitPoint, true);

        $boundary = '----WhatEverBoundaryYouWant';

        // create a new curl resource
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, self::SERVER_IP . self::JOB_SUBMIT);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            // Niet per se nodig: 'Content-Type: multipart/form-data; boundary=' . $boundary
        ]);
            
        // Create multipart array for Switch Submitpoint
        $multipart = [
            [
                'name' => 'flowId',
                'contents' => $submitPoint[0]['flowId'],
            ],
            [
                'name' => 'objectId',
                'contents' => $submitPoint[0]['objectId'],
            ],
            [
                'name' => 'jobName',
                'contents' => 'On_Page_SEO_Checklist_Backlinko.pdf',
            ],
            [
                'name' => 'file[0][path]',
                'contents' => 'On_Page_SEO_Checklist_Backlinko.pdf',
            ],
            [
                'name' => 'file[0][file]',
                'filename' => 'On_Page_SEO_Checklist_Backlinko.pdf',
                'Content-Type' => 'application/pdf',
                'contents' => '@' . self::TEST_FILE
            ]
        ];

        // Add the body / multipart form
        // Niet werkend: curl_setopt($ch, CURLOPT_POSTFIELDS, array($multipart));
        // Niet werkend: curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($multipart));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart);
        // Maakt geen verschil aan of uit: curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        
        // Execute the curl resource
        $result = curl_exec($ch);
        
        if(curl_errno($ch))
        {
            echo 'Curl error: ' . curl_error($ch);
        }

        // close curl resource, and free up system resources
        curl_close($ch);

        return $result;
    }

    protected function multipartBuildQuery($fields, $boundary)
    {
        $retval = '';
        
        foreach($fields as $field)
        {
            $name = $field['name'];
            $contents = $field['contents'];
            $retval .= "$boundary\nContent-Disposition: form-data; name=\"$name\"\r\n\r\n$contents\n";
        }
        $retval .= "$boundary";

        return $retval;
      }
}