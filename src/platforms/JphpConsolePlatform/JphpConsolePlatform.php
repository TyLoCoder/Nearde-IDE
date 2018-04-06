<?php

namespace platforms\JphpConsolePlatform;

use platforms\JphpConsolePlatform\bundles\JPHPRuntimeBundle;
use platforms\JphpConsolePlatform\bundles\JPHPCoreBundle;
use utils\Project;
use utils\AbstractPlatform;

class JphpConsolePlatform extends AbstractPlatform
{
    public function onRegister($project = null)
    {
        $this->registerProjectType(new project\JphpConsoleProjectType()); // project type
        $this->registerRunType(new run\JphpConsoleRunType()); // runing jphp
        
        // builds 
        $this->registerBuildType(new build\JphpConsoleBuildOneJarType()); // onejar
    }
    
    public function getId()
    {
        return __CLASS__;
    }
}