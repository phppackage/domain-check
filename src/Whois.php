<?php
/*
 +-----------------------------------------------------------------------------+
 | PHPPackage - Domain Checker
 +-----------------------------------------------------------------------------+
 | Copyright (c)2018 (http://github.com/phppackage/domaincheck)
 +-----------------------------------------------------------------------------+
 | This source file is subject to MIT License
 | that is bundled with this package in the file LICENSE.
 |
 | If you did not receive a copy of the license and are unable to
 | obtain it through the world-wide-web, please send an email
 | to lawrence@cherone.co.uk so we can send you a copy immediately.
 +-----------------------------------------------------------------------------+
 | Authors:
 |   Lawrence Cherone <lawrence@cherone.co.uk>
 +-----------------------------------------------------------------------------+
 */

namespace PHPPackage\DomainCheck;

class Whois
{
    /**
     * @var
     */
    private $tlds = [];

    /**
     *
     */
    public function __construct(array $tlds = [])
    {
        $this->tlds = $tlds;
    }

    /**
     *
     */
    private function filterServers(array $servers, array $tlds = [])
    {
        $tlds = array_merge($this->tlds, $tlds);

        if (empty($tlds)) {
            return $servers;
        }

        return array_values(array_filter($servers, function ($value) use ($tlds) {
            return in_array($value['tld'], $tlds);
        }));
    }

    /**
     *
     */
    public function servers(array $tlds = [], string $servers_file = 'whois-servers.json')
    {
        $path = __DIR__.'/'.$servers_file;

        if (!file_exists($path) || !is_readable($path)) {
            throw new \RuntimeException('whois-servers.json does not exist or is not readable');
        }

        $json = json_decode(file_get_contents($path), true);

        if (empty($json) || !is_array($json)) {
            throw new \RuntimeException('invalid whois-servers.json file');
        }

        return $this->filterServers($json, $tlds);
    }

    /**
     * Socket connection to whois server.
     *
     * @param string $domain
     * @param string $server
     * @param string $findText
     * @return bool
     */
    public function checkDomain($domain, $server, $pattern)
    {
        // open socket to whois server
        $socket = @fsockopen($server, 43);

        if (!$socket) {
            return false;
        }

        // send the requested domain name
        fputs($socket, $domain."\r\n");

        // read and store the server response
        $response = ' :';

        while (!feof($socket)) {
            $response .= fgets($socket, 256);
        }
        
        // close the connection
        fclose($socket);

        // check the response stream whether the domain is available
        return stripos($response, $pattern) ? true : false;
    }
}
