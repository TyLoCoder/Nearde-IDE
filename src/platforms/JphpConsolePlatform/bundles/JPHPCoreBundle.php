<?php
namespace platforms\JphpConsolePlatform\bundles;

use Types\bundleType;

class JPHPCoreBundle extends bundleType
{
    public function getJarDependances()
    {
        return ['asm-all', 'jphp-core'];
    }
    
    public function getName()
    {
        return "JPHP Core";
    }
    
    public function getVisible()
    {
        return false;
    }
}