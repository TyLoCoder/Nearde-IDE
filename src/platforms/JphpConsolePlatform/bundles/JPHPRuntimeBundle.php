<?php
namespace platforms\JphpConsolePlatform\bundles;

use Types\bundleType;

class JPHPRuntimeBundle extends bundleType
{
    public function getJarDependances()
    {
        return ['jphp-runtime', 'dn-php-sdk', 'gson', 'jphp-json-ext', 'jphp-xml-ext', 'jphp-parser'];
    }
    
    public function getName()
    {
        return "JPHP Runtime";
    }
    
    public function getVisible()
    {
        return false;
    }
}