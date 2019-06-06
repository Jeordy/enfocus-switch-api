<?php

namespace App\Service;

use GuzzleHttp\Client;

/**
 * @class SwitchServiceGuzzle
 * Example class using curl_exec
 */
class SwitchServiceGuzzle
{
    private const SSL_KEY = __DIR__.'/../Key/public.key';

    // Enfocus Switch ip address with port number
    private const SERVER_IP = 'http://localhost:51088';

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

        $client = new Client(['base_uri' => self::SERVER_IP]);

        $result = $client->request('POST', self::LOGIN, ['body' => $jsonBody]);

        return $result->getBody()->getContents();
    }

    /**
     * List all Submitpoints
     *
     * @param string $token
     * @return string|null
     */
    public function listSubmitPoints($token): ?string
    {
        $client = new Client(['base_uri' => self::SERVER_IP]);

        $result = $client->request('GET', self::SUBMIT_POINTS, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        return $result->getBody()->getContents();
    }

    /**
     * Submit file to Switch
     *
     * @param string $token
     * @param string $submitPoint
     * @return string|null
     */
    public function jobSubmit($token, $submitPoint = null): ?string
    {
        $boundary = '----WebKitFormBoundaryrGXxz3Kn1K5R3kAB';

        $submitPoint = \json_decode($submitPoint, true);
        
        $file = file_get_contents(self::TEST_FILE);

        $client = new Client();

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
                'contents' => $file,
            ]
        ];

        $result = $client->request('POST', self::SERVER_IP . self::JOB_SUBMIT, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'multipart/form-data; ' . $boundary,
            ],
            'exceptions' => false,
            'debug' => true,
            'multipart' => $multipart,
        ]);

        $body = $result->getBody();
        while (!$body->eof()) {
            echo $body->read(1024);
        }

        $statusCode = $result->getStatusCode();

        if ($statusCode != 200)
        {
            echo 'Error processing orderline to Switch.' . $statusCode;
            exit;
        }

        return $result->getBody()->getContents();
    }
}