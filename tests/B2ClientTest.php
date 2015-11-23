<?php

namespace B2\Test;

use B2\B2Client;

/**
 * Class B2ClientTest
 * @package B2\Test
 */
class B2ClientTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Test Init
	 */
	public function testInit()
	{

		$CurlRequest = $this->getMockBuilder('\\PartnerIT\Curl\\Network\\CurlRequest')
			->getMock();

		$CurlRequest->method('execute')
			->willReturn('foo');

		$CurlRequest->method('execute')
			->willReturn('foo');

		$CurlRequest->method('setOption')
			->willReturn(true);

		$CurlRequest->method('getErrorNo')
			->willReturn(0);

		$CurlRequest->method('getInfo')
			->will($this->returnValueMap([
				[CURLINFO_HTTP_CODE, 200],
			]));

		$client = new B2Client('myid', 'mykey', $CurlRequest);
		$this->assertInstanceOf('B2\B2Client', $client);
		$this->assertInstanceOf('B2\Files\Files', $client->Files);
	}

	public function testCall()
	{

		/**
		 * @var $client B2Client
		 */
		$client = $this->getMockBuilder('\\B2\\B2Client')
			->setConstructorArgs(['id', 'key'])
			->setMethods(['curl'])
			->getMock();

		$client->setToken(['authorizationToken' => 'token', 'apiUrl' => 'https://url']);

		$client->method('curl')
				->will($this->returnCallback(function() {
					return ['statusCode' => 200, 'responseBody' => ['message' => 'doh']];
				}));


		$result = $client->call('endpoint', 'POST');
		var_dump($result);

	}

}
