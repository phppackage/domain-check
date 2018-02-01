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

class Checker
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
     * Checks the of the name
     * 
     * <code>
     * Array
     *   (
            [mynewdomainnamexyz] => Array
                (
                    [com] => 1
                    [it] => 
                    [net] => 1
                )
        
        )
     * </code>
     * 
     * @param $name
     * @return array
     */
    public function availability(string $name): array
    {
        $name = strtolower(trim($name));
        
        $whois = new Whois();
        
        $result = [];
        $result[$name] = [];
        foreach ($whois->servers($this->tlds) as $server) {
            if ($whois->check(
                $name.'.',
                $server['server'],
                $server['pattern']['available']
            )) {
                // domain is available
                $result[$name][$server['tld']] = true;
            } else {
                // domain is registered
                $result[$name][$server['tld']] = false;
            }
        }
        
        return $result;
    }
}
