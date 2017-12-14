<?php

namespace B2;

use B2\Files\Files;
use PartnerIT\Curl\Network\CurlRequest;

class B2Client
{

    /**
     * @var string
     */
    protected $accountId;

    /**
     * @var string
     */
    protected $applicationKey;

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $authorizationToken;

    /**
     * @var string
     */
    protected $downloadUrl;

    /**
     * @var CurlRequest
     */
    protected $CurlRequest;

    /**
     * @var Files
     */
    public $Files;


    /**
     * B2Client constructor.
     * @param string $accountId
     * @param string $applicationKey
     */
    public function __construct($accountId, $applicationKey, CurlRequest $curlRequest = null)
    {

        if (!$curlRequest) {
            $this->CurlRequest = new CurlRequest();
        } else {
            $this->CurlRequest = $curlRequest;
        }

        $this->accountId      = $accountId;
        $this->applicationKey = $applicationKey;
        $this->Files          = new Files($this);

    }

    /**
     * @param array $result
     */
    public function setToken($result)
    {
        $this->authorizationToken = $result['authorizationToken'];
        $this->apiUrl             = $result['apiUrl'];
        $this->downloadUrl        = $result['downloadUrl'];
    }

    /**
     *
     */
    public function requestToken()
    {

        $response = $this->curl('https://api.backblaze.com/b2api/v1/b2_authorize_account', 'GET', [
            $this->buildBasicAuthHeader()
        ]);

        $data = $response->getJsonData();
        if ($response->getStatusCode() === 200) {
            $this->setToken($data);
            return true;
        } else {
            throw new \RuntimeException('Failed to get token: ' . $data['message']);
        }

    }

    /**
     * @param $endpoint
     * @param $method
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function call($endpoint, $method, $data = [])
    {

        if (empty($this->authorizationToken)) {
            throw new \Exception('You must set or generate a token');
        }

        $headers = [
            $this->buildTokenAuthHeader()
        ];

        $headers[] = 'Content-Type: application/json';
        $headers[] = "Accept: application/json";
        $body      = json_encode($data);

        $response = $this->curl($this->apiUrl . '/b2api/v1/' . $endpoint, $method, $headers, $body);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return $response->getJsonData();
        }

        if ($response->getStatusCode() >= 400) {
            $data = $response->getJsonData();
            throw new \RuntimeException('Error ' . $response->getStatusCode() . ' - ' . $data['message']);
        }

    }

    /**
     * @param $uri
     * @param string $method
     * @param array $headers
     * @param null $body
     * @return B2Response
     */
    public function curl($uri, $method = 'GET', $headers = [], $body = null)
    {

        $response = new B2Response();

        if (!is_resource($this->CurlRequest->getHandle())) {
            $this->CurlRequest->init();
        }

        $this->CurlRequest->setOption(CURLOPT_URL, $uri);
        $this->CurlRequest->setOption(CURLOPT_CUSTOMREQUEST, $method);
        $this->CurlRequest->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->CurlRequest->setOption(CURLOPT_POST, 1);
        $this->CurlRequest->setOption(CURLOPT_POSTFIELDS, $body);
        $this->CurlRequest->setOption(CURLOPT_HTTPHEADER, $headers);
        $this->CurlRequest->setOption(CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use ($response) {
                $response->addHeader($header);

                return strlen($header);
            });

        $resp = $this->CurlRequest->execute();
        if ($this->CurlRequest->getErrorNo() !== 0) {
            $this->CurlRequest->close();
            throw new \RuntimeException('curl error ' . $this->CurlRequest->getError() . '" - Code: ' . $this->CurlRequest->getErrorNo());
        } else {
            $response->setData($resp);
            $response->setStatusCode($this->CurlRequest->getInfo(CURLINFO_HTTP_CODE));
            $this->CurlRequest->close();

            return $response;
        }
    }

    /**
     * @return string
     */
    public function buildBasicAuthHeader()
    {
        return 'Authorization: Basic ' . base64_encode($this->accountId . ':' . $this->applicationKey);
    }

    /**
     * @return string
     */
    public function buildTokenAuthHeader()
    {
        return 'Authorization: ' . $this->authorizationToken;
    }

    /**
     * @param $data
     * @param $sha1
     * @param $fileName
     * @param $url
     * @param $token
     */
    public function uploadData($fileData, $fileDataSha1, $fileName, $contentType, $uploadUrl, $uploadToken)
    {
        $headers   = [];
        $headers[] = "Authorization: " . $uploadToken;
        $headers[] = "X-Bz-File-Name: " . $fileName;
        $headers[] = "Content-Type: " . $contentType;
        $headers[] = "X-Bz-Content-Sha1: " . $fileDataSha1;

        $response = $this->curl($uploadUrl, 'POST', $headers, $fileData);
        return $response->getJsonData();
    }

    /**
     * @param $url
     */
    public function downloadFileByName($uri)
    {

        $uri     = $this->downloadUrl . "/file/" . $uri;
        $headers = [
            $this->buildTokenAuthHeader()
        ];

        $response = $this->curl($uri, 'GET', $headers);
        if ($response->getStatusCode() === 200) {
            return $response->getData();
        } else {
            throw new \RuntimeException('Download failed. ' . $response->getStatusCode());
        }
    }

    /**
     * @return string
     */
    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }

    public function __destruct()
    {
        if ($this->CurlRequest && is_resource($this->CurlRequest->getHandle())) {
            $this->CurlRequest->close();
        }
    }
}