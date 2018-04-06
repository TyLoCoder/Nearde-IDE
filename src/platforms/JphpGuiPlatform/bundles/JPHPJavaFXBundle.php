<?php
namespace platforms\JphpGuiPlatform\bundles;

use Types\bundleType;

class JPHPJavaFXBundle extends bundleType
{
    public function getJarDependances()
    {
        return [
            'jphp-gui-ext', 'jphp-desktop-ext', 'jphp-zend-ext', 'jphp-app-framework',
        ];
    }
    
    public function getName()
    {
        return "JPHP JavaFX GUI";
    }
    
    public function getVisible()
    {
        return false;
    }
}