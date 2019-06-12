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

    private const FILE_PATH = __DIR__.'/../Files/';

    private const FILE_NAME = 'curl_test_file.pdf';

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

        $eol = "\r\n";
        $boundary=md5(time());

        $fileContent= file_get_contents(self::FILE_PATH . self::FILE_NAME);
        
        // Create the POST body text
        $data = '';

        $data .= '--' . $boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="file"; filename="'. self::FILE_NAME .'"' . $eol;
        $data .= 'Content-Type: application/pdf' . $eol;
        $data .= $eol . $fileContent . $eol;
        $data .= '--' . $boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="flowId"' . $eol . $eol;
        $data .= $submitPoint[0]['flowId'] . $eol;
        // $data .= '--' . $boundary . $eol;
        // $data .= 'Content-Disposition: form-data; name="modified"' . $eol . $eol;
        // $data .= $modified . $eol;
        $data .= '--' . $boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="objectId"' . $eol . $eol;
        $data .= $submitPoint[0]['objectId'] . $eol;
        $data .= '--' . $boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="filePath"' . $eol . $eol;
        $data .= self::FILE_NAME . $eol;
        $data .= '--' . $boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="jobName"' . $eol . $eol;
        $data .= self::FILE_NAME . $eol;
        $data .= "--" . $boundary . "--" . $eol . $eol;

        //POST with file_get_contents
	    $params = [
            'http' => [
                'method' => 'POST',
                'header' => "Authorization: Bearer " . $token . "\r\n" .
                'Content-Type: multipart/form-data; boundary=' . $boundary,
                'content' => $data
            ]
        ];
    
        $ctx = stream_context_create($params);
        $result = file_get_contents(self::SERVER_IP . self::JOB_SUBMIT, null, $ctx);
        
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