<?php

namespace w3lifer\google;

use Exception;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

/**
 * @see https://github.com/googleapis/google-api-php-client/blob/master/examples/simple-file-upload.php
 */
class GoogleDrive
{
    private $pathToCredentials;

    private $pathToToken;

    private $client;

    private $service;

    public function __construct($config)
    {
        if (
            !empty($config['pathToCredentials']) &&
            file_exists($config['pathToCredentials'])
        ) {
            $this->pathToCredentials = $config['pathToCredentials'];
        } else {
            throw new Exception('Incorrect path to credentials');
        }

        if (empty($config['pathToToken'])) {
            throw new Exception('The path to token can not be empty');
        }
        $this->pathToToken = $config['pathToToken'];

        $this->client = new Google_Client();

        $this->client->setApplicationName('');
        $this->client->setAccessType('offline');

        $this->client->setAuthConfig($this->pathToCredentials);
        $this->client->addScope('https://www.googleapis.com/auth/drive');

        $this->checkAccessToken();

        $this->service = new Google_Service_Drive($this->client);
    }

    private function checkAccessToken()
    {
        if (file_exists($this->pathToToken)) {
            $accessToken = json_decode(
                file_get_contents($this->pathToToken),
                true
            );
            $this->client->setAccessToken($accessToken);
        }
        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            } else {
                $authUrl = $this->client->createAuthUrl();
                echo 'Open the following link in your browser:' . "\n" . $authUrl . "\n";
                echo 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));
                $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
                $this->client->setAccessToken($accessToken);
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            if (!file_exists(dirname($this->pathToToken))) {
                mkdir(dirname($this->pathToToken), 0700, true);
            }
            file_put_contents($this->pathToToken, json_encode($this->client->getAccessToken()));
        }
    }

    public function upload(string $pathToFile, array $folderIds = [])
    {
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => basename($pathToFile),
            'parents' => $folderIds,
        ]);
        $result = $this->service->files->create(
            $fileMetadata,
            [
                'data' => file_get_contents($pathToFile),
                'mimeType' => 'application/octet-stream',
                'uploadType' => 'multipart',
            ]
        );
        return $result->id;
    }
}
