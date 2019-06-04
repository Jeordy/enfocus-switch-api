<?php

namespace App\Service;

class SwitchService
{
    private const SSL_KEY = __DIR__.'/../Key/public.key';

    private const SERVER_IP = '127.0.0.1';

    private const SERVER_PORT = '51088';

    private const LOGIN = 'login';

    /**
     * Generate encrypted password
     *
     * @param string $password
     * @return string
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
     * @return string
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
        curl_setopt($ch, CURLOPT_URL, self::SERVER_IP . ':' . self::SERVER_PORT . '/' . self::LOGIN);
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
}