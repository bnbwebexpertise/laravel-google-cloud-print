<?php
namespace Bnb\GoogleCloudPrint;

use Bnb\GoogleCloudPrint\Exceptions\InvalidCredentialsException;
use Google_Client;
use Illuminate\Contracts\Config\Repository as Config;

class GoogleCloudPrint
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Google_Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $credentialsPath;


    public function __construct(Config $config)
    {
        $this->config = $config;
    }


    protected function setUpClient()
    {
        if ($this->client === null) {
            $credentialsPath = $this->credentialsPath ?: $this->config->get('gcp.credentials');

            if ( ! preg_match('/^\//', $credentialsPath)) {
                $credentialsPath = base_path($credentialsPath);
            }

            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

            $this->client = new Google_Client();
            $this->client->useApplicationDefaultCredentials();
            $this->client->addScope(['https://www.googleapis.com/auth/cloudprint']);
        }
    }


    protected function requireToken()
    {
        $this->setUpClient();

        if ($this->client->isAccessTokenExpired()) {
            $this->client->refreshTokenWithAssertion();
        }

        if ( ! ($accessToken = $this->client->getAccessToken())) {
            throw new InvalidCredentialsException();
        }

        return $accessToken;
    }


    public function setCredentialsPath($credentialsPath)
    {
        $this->client = null;
        $this->credentialsPath = $credentialsPath;
    }


    public function getAccessToken()
    {
        $accessToken = $this->requireToken();

        return $accessToken['access_token'];
    }


    public function asText()
    {
        return new PrintTask($this->getAccessToken(), 'text/plain');
    }


    public function asHtml()
    {
        return new PrintTask($this->getAccessToken(), 'text/html');
    }


    public function asPdf()
    {
        return new PrintTask($this->getAccessToken(), 'application/pdf');
    }
}