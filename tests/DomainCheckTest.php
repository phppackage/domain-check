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
    public function testAvailability()
    {
        //
        $checker = new Checker();
        
        $this->assertTrue($checker->availability('foobar'));
    }

    /**
     *
     */
    public function testCheckDomainTrue()
    {
        //
        $checker = new Checker();
        
        // open private method
        $class = new \ReflectionClass($checker);
        $method = $class->getMethod('checkDomain');
        $method->setAccessible(true);
        
        $fsockopen = $this->getFunctionMock(__NAMESPACE__, "fsockopen");
        $fsockopen->expects($this->any())->willReturnCallback( // first time
            function ($server, $port) {
                $this->assertEquals("whois.server.test", $server);
                $this->assertEquals(43, $port);
                return true;
            }
        );

        $fputs = $this->getFunctionMock(__NAMESPACE__, "fputs");
        $fputs->expects($this->once())->willReturnCallback(
            function ($socket, $data) {
                $this->assertTrue($socket); // this comes from mock
                $this->assertEquals("domain\r\n", $data);
                return true;
            }
        );
        
        $feof = $this->getFunctionMock(__NAMESPACE__, "feof");
        $feof->expects($this->at(0))->willReturnCallback( // first loop
            function ($socket) {
                return false;
            }
        );
        $feof->expects($this->at(1))->willReturnCallback( // second loop
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
        
        $this->assertTrue($method->invoke($checker, 'domain', 'whois.server.test', 'not found'));
    }
    
    /**
     *
     */
    public function testCheckDomainFalseDomainTaken()
    {
        //
        $checker = new Checker();
        
        // open private method
        $class = new \ReflectionClass($checker);
        $method = $class->getMethod('checkDomain');
        $method->setAccessible(true);
        
        $fsockopen = $this->getFunctionMock(__NAMESPACE__, "fsockopen");
        $fsockopen->expects($this->once())->willReturnCallback( // first time
            function ($server, $port) {
                $this->assertEquals("whois.server.test", $server);
                $this->assertEquals(43, $port);
                return true;
            }
        );

        $fputs = $this->getFunctionMock(__NAMESPACE__, "fputs");
        $fputs->expects($this->once())->willReturnCallback(
            function ($socket, $data) {
                $this->assertTrue($socket); // this comes from mock
                $this->assertEquals("domain\r\n", $data);
                return true;
            }
        );
        
        $feof = $this->getFunctionMock(__NAMESPACE__, "feof");
        $feof->expects($this->at(0))->willReturnCallback( // first loop
            function ($socket) {
                return false;
            }
        );
        $feof->expects($this->at(1))->willReturnCallback( // second loop
            function ($socket) {
                return true;
            }
        );
        
        $fgets = $this->getFunctionMock(__NAMESPACE__, "fgets");
        $fgets->expects($this->any())->willReturnCallback(
            function ($socket, $length) {
                $this->assertEquals(256, $length);
                return 'Large whois response';
            }
        );
        
        $fclose = $this->getFunctionMock(__NAMESPACE__, "fclose");
        $fclose->expects($this->once())->willReturnCallback(
            function ($socket) {
                $this->assertTrue($socket);
            }
        );
        
        $this->assertFalse($method->invoke($checker, 'domain', 'whois.server.test', 'not found'));
    }

    /**
     *
     */
    public function testCheckDomainFalseNoConnection()
    {
        //
        $checker = new Checker();
        
        // open private method
        $class = new \ReflectionClass($checker);
        $method = $class->getMethod('checkDomain');
        $method->setAccessible(true);
        
        $fsockopen = $this->getFunctionMock(__NAMESPACE__, "fsockopen");
        $fsockopen->expects($this->any())->willReturnCallback(
            function ($server, $port) {
                $this->assertEquals("whois.server.test", $server);
                $this->assertEquals(43, $port);
                return false;
            }
        );

        $this->assertFalse($method->invoke($checker, 'domain', 'whois.server.test', 'not found'));
    }
}
