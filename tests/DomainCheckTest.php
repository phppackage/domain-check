<?php

namespace PHPPackage\DomainCheck;

use PHPUnit\Framework\TestCase;

class DomainCheckTest extends TestCase
{
    use \phpmock\phpunit\PHPMock;

    /**
     *
     */
    public function testObjectInstanceOf()
    {
        $this->assertInstanceOf(
            'PHPPackage\DomainCheck\Checker',
            new Checker()
        );
    }

    /**
     *
     */
    public function testConstruct()
    {
        //
        $this->assertClassHasAttribute('tlds', 'PHPPackage\DomainCheck\Checker');

        //
        $checker = new Checker(['uk']);

        //
        $this->assertInternalType('array', \PHPUnit\Framework\Assert::readAttribute($checker, 'tlds'));
    }

    /**
     *
     */
    public function testWhoisServers()
    {
        $checker = new Checker();

        $result = $checker->whoisServers(['com']);

        $this->assertEquals(1, count($result));
    }
    
    /**
     *
     */
    public function testWhoisServersAll()
    {
        $checker = new Checker();

        $result = $checker->whoisServers();

        $this->assertEquals(true, (count($result) > 1000));
    }

    /**
     *
     */
    public function testWhoisServersNotExists()
    {
        $checker = new Checker();

        try {
            $checker->whoisServers(['com'], '../tests/fixtures/not-exists-whois-servers.json');
        } catch (\Exception $e) {
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertEquals('whois-servers.json does not exist or is not readable', $e->getMessage());
        }
    }
    
    /**
     *
     */
    public function testWhoisServersEmpty()
    {
        $checker = new Checker();

        try {
            $checker->whoisServers(['com'], '../tests/fixtures/empty-whois-servers.json');
        } catch (\Exception $e) {
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertEquals('invalid whois-servers.json file', $e->getMessage());
        }
    }

    /**
     *
     */
    public function testWhoisServersInvalid()
    {
        $checker = new Checker();

        try {
            $checker->whoisServers(['com'], '../tests/fixtures/invalid-whois-servers.json');
        } catch (\Exception $e) {
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertEquals('invalid whois-servers.json file', $e->getMessage());
        }
    }
    
    /**
     *
     */
    public function testAvailabilityNotFound()
    {
        //
        $checker = new Checker(['com']);
        
        $fsockopen = $this->getFunctionMock(__NAMESPACE__, "fsockopen");
        $fsockopen->expects($this->any())->willReturnCallback(
            function ($server, $port) {
                $this->assertEquals("whois.crsnic.net", $server);
                $this->assertEquals(43, $port);
                return true;
            }
        );

        $fputs = $this->getFunctionMock(__NAMESPACE__, "fputs");
        $fputs->expects($this->once())->willReturnCallback(
            function ($socket, $data) {
                $this->assertTrue($socket);
                $this->assertEquals("testdomain.\r\n", $data);
                return true;
            }
        );
        
        $feof = $this->getFunctionMock(__NAMESPACE__, "feof");
        $feof->expects($this->at(0))->willReturnCallback(
            function ($socket) {
                return false;
            }
        );
        $feof->expects($this->at(1))->willReturnCallback(
            function ($socket) {
                return true;
            }
        );
        
        $fgets = $this->getFunctionMock(__NAMESPACE__, "fgets");
        $fgets->expects($this->any())->willReturnCallback(
            function ($socket, $length) {
                $this->assertEquals(256, $length);
                return 'not found';
            }
        );
        
        $fclose = $this->getFunctionMock(__NAMESPACE__, "fclose");
        $fclose->expects($this->once())->willReturnCallback(
            function ($socket) {
                $this->assertTrue($socket);
            }
        );
        
        $this->assertEquals(['testdomain' => ['com' => false]], $checker->availability('testdomain'));
    }
    
    /**
     *
     */
    public function testAvailabilityFound()
    {
        //
        $checker = new Checker(['com']);
        
        $fsockopen = $this->getFunctionMock(__NAMESPACE__, "fsockopen");
        $fsockopen->expects($this->any())->willReturnCallback(
            function ($server, $port) {
                $this->assertEquals("whois.crsnic.net", $server);
                $this->assertEquals(43, $port);
                return true;
            }
        );

        $fputs = $this->getFunctionMock(__NAMESPACE__, "fputs");
        $fputs->expects($this->once())->willReturnCallback(
            function ($socket, $data) {
                $this->assertTrue($socket);
                $this->assertEquals("testdomain.\r\n", $data);
                return true;
            }
        );
        
        $feof = $this->getFunctionMock(__NAMESPACE__, "feof");
        $feof->expects($this->at(0))->willReturnCallback(
            function ($socket) {
                return false;
            }
        );
        $feof->expects($this->at(1))->willReturnCallback(
            function ($socket) {
                return true;
            }
        );
        
        $fgets = $this->getFunctionMock(__NAMESPACE__, "fgets");
        $fgets->expects($this->any())->willReturnCallback(
            function ($socket, $length) {
                $this->assertEquals(256, $length);
                return 'no match for';
            }
        );
        
        $fclose = $this->getFunctionMock(__NAMESPACE__, "fclose");
        $fclose->expects($this->once())->willReturnCallback(
            function ($socket) {
                $this->assertTrue($socket);
            }
        );
        
        $this->assertEquals(['testdomain' => ['com' => true]], $checker->availability('testdomain'));
    }

}
