## Domain Checker

[![Build Status](https://travis-ci.org/phppackage/domain-check.svg?branch=master)](https://travis-ci.org/phppackage/domain-check)
[![StyleCI](https://styleci.io/repos/119786906/shield?branch=master)](https://styleci.io/repos/119786906)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phppackage/domain-check/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phppackage/domain-check/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/phppackage/domain-check/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/phppackage/domain-check/code-structure/master/code-coverage)
[![Packagist Version](https://img.shields.io/packagist/v/phppackage/domain-check.svg?style=flat-square)](https://github.com/phppackage/domain-check/releases)
[![Packagist Downloads](https://img.shields.io/packagist/dt/phppackage/domain-check.svg?style=flat-square)](https://packagist.org/packages/phppackage/domain-check)

**WIP:** Domain availability checker.


## Install

Require this package with composer using the following command:

``` bash
$ composer require phppackage/domain-check
```

### Usage example:

    <?php
    require 'vendor/autoload.php';
    
    use \PHPPackage\domain-check\Checker;
    
    //
    $checker = new Checker();
    
    /**
     * Loolup all tlds for a given name, e.g domain.com, domain.co.uk ...
     * @return array
     */
    $checker->availability('domain');
    
    

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Credits

 - [Lawrence Cherone](http://github.com/phppackage)
 - [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
