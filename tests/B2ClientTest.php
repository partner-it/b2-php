<?php

namespace B2\Test;

use B2\B2Client;
use B2\B2Response;

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
        $client = new B2Client('myid', 'mykey');
        $this->assertInstanceOf('B2\B2Client', $client);
        $this->assertInstanceOf('B2\Files\Files', $client->Files);
    }

    public function testCurlWrapper()
    {

        $CurlRequest = $this->getMockBuilder('\\PartnerIT\Curl\\Network\\CurlRequest')
            ->getMock();
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

        $result = $client->curl('url', 'GET', ['header' => 'value'], 'mybody');
        $this->assertInstanceOf('\\B2\\B2Response', $result);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('foo', $result->getData());

    }

    public function testCallSuccess()
    {

        /**
         * @var $client B2Client
         */
        $client = $this->getMockBuilder('\\B2\\B2Client')
            ->setConstructorArgs(['id', 'key'])
            ->setMethods(['curl'])
            ->getMock();

        $client->setToken([
            'authorizationToken' => 'token',
            'apiUrl'             => 'https://url',
            'downloadUrl'        => 'https://downloadurl'
        ]);

        $client->method('curl')
            ->will($this->returnCallback(function () {
                $response = new B2Response();
                $response->setStatusCode(200);
                $response->setData(json_encode(['message' => 'doh']));
                return $response;
            }));

        $result = $client->call('endpoint', 'POST');
        $this->assertEquals(['message' => 'doh'], $result);

    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage This is a serverside error message
     */
    public function testCallFailure()
    {

        /**
         * @var $client B2Client
         */
        $client = $this->getMockBuilder('\\B2\\B2Client')
            ->setConstructorArgs(['id', 'key'])
            ->setMethods(['curl'])
            ->getMock();

        $client->setToken([
            'authorizationToken' => 'token',
            'apiUrl'             => 'https://url',
            'downloadUrl'        => 'https://downloadurl'
        ]);

        $client->method('curl')
            ->will($this->returnCallback(function () {

                $response = new B2Response();
                $response->setStatusCode(400);
                $response->setData(json_encode(['message' => 'This is a serverside error message']));
                return $response;
            }));

        $result = $client->call('endpoint', 'POST');
        $this->assertEquals(['message' => 'doh'], $result);

    }

    public function testRequestToken()
    {

        /**
         * @var $client B2Client
         */
        $client = $this->getMockBuilder('\\B2\\B2Client')
            ->setConstructorArgs(['id', 'key'])
            ->setMethods(['curl'])
            ->getMock();

        $client->method('curl')
            ->will($this->returnCallback(function () {
                $response = new B2Response();
                $response->setStatusCode(200);
                $response->setData(json_encode([
                    'downloadUrl'        => 'https://downloadurl',
                    'apiUrl'             => 'api',
                    'authorizationToken' => 'This is a serverside error message'
                ]));
                return $response;
            }));

        $result = $client->requestToken();
        $this->assertTrue($result);
        $this->assertEquals('https://downloadurl', $client->getDownloadUrl());

    }

}
