<?php
declare(strict_types=1);

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
    private function filterServers(array $servers, array $tlds = []): array
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
    private function loadServers(string $servers_file = 'whois-servers.json')
    {
        $path = __DIR__.'/'.$servers_file;

        if (!file_exists($path) || !is_readable($path)) {
            throw new \RuntimeException('whois-servers.json does not exist or is not readable');
        }
        
        return json_decode(file_get_contents($path), true);
    }

    /**
     *
     */
    public function servers(array $tlds = [], bool $all = false, string $servers_file = 'whois-servers.json'): array
    {
        $json = $this->loadServers($servers_file);

        if (empty($json) || !is_array($json)) {
            throw new \RuntimeException('invalid whois-servers.json file');
        }

        if (!$all) {
            return $this->filterServers($json, $tlds);
        }
        
        return $json;
    }
    
    /**
     *
     */
    public function allServers(string $servers_file = 'whois-servers.json'): array
    {
        return $this->servers([], true, $servers_file);
    }

    /**
     * Socket connection to whois server.
     *
     * @param string $domain
     * @param string $server
     * @param string $findText
     * @return bool
     */
    public function check($domain, $server, $pattern): bool
    {
        $socket = fsockopen($server, 43);

        if ($socket === false) {
            return false;
        }

        fputs($socket, $domain."\r\n");

        $response = null;
        while (!feof($socket)) {
            $response .= fgets($socket, 512);
        }

        fclose($socket);

        return (stripos($response, $pattern) !== false);
    }
}
