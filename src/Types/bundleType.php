<?php
namespace Types;

abstract class bundleType 
{
    abstract function getJarDependances();
    
    abstract function getName();
    
    public function getVisible()
    {
        return true;
    }
}