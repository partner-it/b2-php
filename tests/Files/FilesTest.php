<?php
namespace B2\Test\Files;

use B2\Files\Files;

/**
 * Class FilesTest
 * @package B2\Test\Files
 */
class FilesTest extends \PHPUnit_Framework_TestCase
{

    public function testInit()
    {
        $client = $this->getMockBuilder('\\B2\\B2Client')
            ->setConstructorArgs(['id', 'key'])
            ->getMock();
        $Files  = new Files($client);
        $this->assertInstanceOf('\\B2\\B2Client', $Files->B2Client);
    }

}