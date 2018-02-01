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
     * @covers PHPPackage\DomainCheck\Whois::servers
     * @covers PHPPackage\DomainCheck\Whois::filterServers
     */
    public function testServersJustCom()
    {
        $whois = new Whois();

        $result = $whois->servers(['com']);

        $this->assertEquals(1, count($result));
    }

    /**
     * @covers PHPPackage\DomainCheck\Whois::loadServers
     */
    public function testLoadServers()
    {
        //
        $whois = new Whois();

        // open private method
        $class = new \ReflectionClass($whois);
        $method = $class->getMethod('loadServers');
        $method->setAccessible(true);

        $servers = $method->invoke($whois, 'whois-servers.json');

        $this->assertInternalType('array', $servers);
        $this->assertEquals(true, (count($servers) > 1000));
    }

    /**
     * @covers PHPPackage\DomainCheck\Whois::servers
     * @covers PHPPackage\DomainCheck\Whois::filterServers
     */
    public function testServersAll()
    {
        $whois = new Whois();

        $servers = $whois->servers();

        $this->assertInternalType('array', $servers);
        $this->assertEquals(true, (count($servers) > 1000));
    }

    /**
     * @covers PHPPackage\DomainCheck\Whois::servers
     * @covers PHPPackage\DomainCheck\Whois::filterServers
     */
    public function testServersAllByParams()
    {
        $whois = new Whois();

        $servers = $whois->servers([], true, 'whois-servers.json');

        $this->assertInternalType('array', $servers);
        $this->assertEquals(true, (count($servers) > 1000));
    }

    /**
     * @covers PHPPackage\DomainCheck\Whois::allServers
     * @covers PHPPackage\DomainCheck\Whois::servers
     * @covers PHPPackage\DomainCheck\Whois::filterServers
     */
    public function testServersAllByMethod()
    {
        $whois = new Whois();

        $servers = $whois->allServers('whois-servers.json');

        $this->assertInternalType('array', $servers);
        $this->assertEquals(true, (count($servers) > 1000));
    }

    /**
     * @covers PHPPackage\DomainCheck\Whois::servers
     * @covers PHPPackage\DomainCheck\Whois::loadServers
     */
    public function testServersNotExists()
    {
        $whois = new Whois();

        try {
            $whois->servers(['com'], false, '../tests/fixtures/not-exists-whois-servers.json');
        } catch (\Exception $e) {
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertEquals('whois-servers.json does not exist or is not readable', $e->getMessage());
        }
    }

    /**
     * @covers PHPPackage\DomainCheck\Whois::servers
     * @covers PHPPackage\DomainCheck\Whois::loadServers
     */
    public function testServersEmpty()
    {
        $whois = new Whois();

        try {
            $whois->servers(['com'], false, '../tests/fixtures/empty-whois-servers.json');
        } catch (\Exception $e) {
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertEquals('invalid whois-servers.json file', $e->getMessage());
        }
    }

    /**
     * @covers PHPPackage\DomainCheck\Whois::servers
     * @covers PHPPackage\DomainCheck\Whois::loadServers
     */
    public function testServersInvalid()
    {
        $whois = new Whois();

        try {
            $whois->servers(['com'], false, '../tests/fixtures/invalid-whois-servers.json');
        } catch (\Exception $e) {
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertEquals('invalid whois-servers.json file', $e->getMessage());
        }
    }

    /**
     * @covers PHPPackage\DomainCheck\Whois::check
     */
    public function testcheckTrue()
    {
        //
        $whois = new Whois();

        // open private method
        $class = new \ReflectionClass($whois);
        $method = $class->getMethod('check');
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
                $this->assertEquals(512, $length);
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
     * @covers PHPPackage\DomainCheck\Whois::check
     */
    public function testcheckFalseDomainTaken()
    {
        //
        $whois = new Whois();

        // open private method
        $class = new \ReflectionClass($whois);
        $method = $class->getMethod('check');
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
                $this->assertEquals(512, $length);
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
     * @covers PHPPackage\DomainCheck\Whois::check
     */
    public function testcheckFalseNoConnection()
    {
        //
        $whois = new Whois();

        // open private method
        $class = new \ReflectionClass($whois);
        $method = $class->getMethod('check');
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
