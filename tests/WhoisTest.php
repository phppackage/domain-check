<?php

namespace PHPPackage\DomainCheck;

use PHPUnit\Framework\TestCase;

class WhoisTest extends TestCase
{
    use \phpmock\phpunit\PHPMock;

    /**
     *
     */
    public function testObjectInstanceOf()
    {
        $this->assertInstanceOf(
            'PHPPackage\DomainCheck\Whois',
            new Whois()
        );
    }

    /**
     *
     */
    public function testCheckDomainTrue()
    {
        //
        $whois = new Whois();
        
        // open private method
        $class = new \ReflectionClass($whois);
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
        
        $this->assertTrue($method->invoke($whois, 'domain', 'whois.server.test', 'not found'));
    }
    
    /**
     *
     */
    public function testCheckDomainFalseDomainTaken()
    {
        //
        $whois = new Whois();
        
        // open private method
        $class = new \ReflectionClass($whois);
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
        
        $this->assertFalse($method->invoke($whois, 'domain', 'whois.server.test', 'not found'));
    }

    /**
     *
     */
    public function testCheckDomainFalseNoConnection()
    {
        //
        $whois = new Whois();
        
        // open private method
        $class = new \ReflectionClass($whois);
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

        $this->assertFalse($method->invoke($whois, 'domain', 'whois.server.test', 'not found'));
    }
}
