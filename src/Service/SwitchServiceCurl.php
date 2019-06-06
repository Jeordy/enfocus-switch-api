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
    private const SERVER_IP = 'http://localhost:51088';

    private const LOGIN = '/login';

    private const SUBMIT_POINTS = '/api/v1/submitpoints';

    private const JOB_SUBMIT = '/api/v1/job';

    private const TEST_FILE = __DIR__.'/../Files/On_Page_SEO_Checklist_Backlinko.pdf';

    private $eol = "\r\n";

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
        var_dump('!@$' . base64_encode($encrypted));
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
        $boundary = '----WebKitFormBoundaryrGXxz3Kn1K5R3kAB';

        $submitPoint = json_decode($submitPoint, true);
        
        $file = file_get_contents(self::TEST_FILE);

        ob_start();  
        $out = fopen('php://output', 'w');

        // create a new curl resource
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, self::SERVER_IP . self::JOB_SUBMIT . '/metadata');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data; boundary='.$boundary,
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_VERBOSE, true);  
        curl_setopt($ch, CURLOPT_STDERR, $out); 

        // Create multipart for Switch Submitpoint
        $multipart = $boundary . $this->eol . 'Content-Disposition=form-data; name="flowId";' . $this->eol . $this->eol . $submitPoint[0]['flowId'] . $this->eol;
        $multipart .= $boundary . $this->eol . 'Content-Disposition=form-data; name="objectId";' . $this->eol . $this->eol . $submitPoint[0]['objectId'] . $this->eol;
        $multipart .= $boundary . $this->eol . 'Content-Disposition=form-data; name="jobName";' . $this->eol . $this->eol . 'On_Page_SEO_Checklist_Backlinko.pdf' . $this->eol;
        $multipart .= $boundary . $this->eol . 'Content-Disposition=form-data; name="file[0][path]";' . $this->eol . $this->eol . 'On_Page_SEO_Checklist_Backlinko.pdf' . $this->eol;
        $multipart .= $boundary . $this->eol . 'Content-Disposition=form-data; name="file[0][file]"; filename=On_Page_SEO_Checklist_Backlinko.pdf; Content-Type=application/pdf;' . $this->eol . $this->eol . $file . $this->eol;

        // Add the json body
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart); 
        
        // Execute the curl resource
        $result = curl_exec($ch);

        // $info = curl_getinfo($ch);
        // var_dump($info);
        //print_r($info['request_header']);
        // $err = curl_error($ch);
        // var_dump($err);

        fclose($out);  
        $debug = ob_get_clean();

        var_dump($debug);

        // close curl resource, and free up system resources
        curl_close($ch);

        return $result;
    }
}