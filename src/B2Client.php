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
		$this->Files = new Files($this);

	}

	/**
	 * @param array $result
	 */
	public function setToken($result) {
		$this->authorizationToken = $result['authorizationToken'];
		$this->apiUrl = $result['apiUrl'];
	}

	/**
	 *
	 */
	public function requestToken()
	{

		$results = $this->curl('https://api.backblaze.com/b2api/v1/b2_authorize_account', 'GET', [
			$this->buildBasicAuthHeader()
		]);

		if ($results['statusCode'] === 200) {
			$this->setToken($results['responseBody']);
		} else {
			throw new \RuntimeException('Failed to get token: ' . $results['responseBody']['message']);
		}

	}

	/**
	 * @param $endpoint
	 * @param $method
	 * @param array $data
	 * @return mixed
	 * @throws \Exception
	 */
    public function call($endpoint, $method, $data = [], $headers = [])
	{

		if (empty($this->authorizationToken)) {
			throw new \Exception('You must set or generate a token');
		}

        if (!$headers) {
            $headers = [
                $this->buildTokenAuthHeader()
            ];
        }

		$result = $this->curl($this->apiUrl . '/b2api/v1/' . $endpoint, $method, $headers, $data);

		if ($result['statusCode'] >= 200 && $result['statusCode'] < 300) {
			return $result;
		}

		if ($result['statusCode'] >= 400) {
			throw new \RuntimeException('Error' . $result['statusCode'] . ' - ' . $result['responseBody']['message']);
		}

	}

	/**
	 * @param $uri
	 * @param string $method
	 * @param array $headers
	 * @param array $body
	 * @return array
	 * @throws \Exception
	 */
	public function curl($uri, $method = 'GET', $headers = [], $body = [])
	{

		$this->CurlRequest->setOption(CURLOPT_URL, $uri);
		$this->CurlRequest->setOption(CURLOPT_CUSTOMREQUEST, $method);
		$this->CurlRequest->setOption(CURLOPT_RETURNTRANSFER, 1);

		$headers[] = 'Content-Type: application/json';
		$headers[] = "Accept: application/json";

		$this->CurlRequest->setOption(CURLOPT_POST, 1);
		$body      = json_encode($body);
		$this->CurlRequest->setOption(CURLOPT_POSTFIELDS, $body);
		$this->CurlRequest->setOption(CURLOPT_HTTPHEADER, $headers);

		$resp = $this->CurlRequest->execute();
		if ($this->CurlRequest->getErrorNo() !== 0) {
			throw new \RuntimeException('curl error ' . $this->CurlRequest->getError() . '" - Code: ' . $this->CurlRequest->getErrorNo());
		} else {
			return [
				'statusCode'   => $this->CurlRequest->getInfo(CURLINFO_HTTP_CODE),
				'responseBody' => json_decode($resp, true)
			];
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

}