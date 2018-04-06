<?php

namespace platforms\JphpGuiPlatform;

use platforms\JphpGuiPlatform\bundles\JPHPJavaFXBundle;
use platforms\JphpConsolePlatform\bundles\JPHPRuntimeBundle;
use platforms\JphpConsolePlatform\bundles\JPHPCoreBundle;
use utils\Project;
use platforms\JphpConsolePlatform\build\JphpConsoleBuildOneJarType;
use platforms\JphpConsolePlatform\run\JphpConsoleRunType;
use php\framework\Logger;
use platforms\JphpGuiPlatform\project\JphpGuiProjectType;
use platforms\JphpGuiPlatform\run\JphpGuiRunType;
use platforms\JphpGuiPlatform\build\JphpGuiBuildOneJarType;
use utils\AbstractPlatform;

class JphpGuiPlatform extends AbstractPlatform
{
    public function onRegister($project = null)
    {
        $this->registerProjectType(new JphpGuiProjectType()); // project type
        $this->registerRunType(new JphpConsoleRunType()); // runing jphp
        
        // builds 
        $this->registerBuildType(new JphpConsoleBuildOneJarType()); // onejar
    }
    
    public function getId()
    {
        return __CLASS__;
    }
}