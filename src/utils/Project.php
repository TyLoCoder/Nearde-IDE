<?php
namespace utils;

use platforms\JphpGuiPlatform\JphpGuiPlatform;
use platforms\JphpConsolePlatform\JphpConsolePlatform;
use Types\bundleType;
use php\framework\Logger;
use app;
use facade\Json;
use \php\lang\Process;

class Project 
{
    private $dir;
    private $name;
    private $type;
    private $platform;
    private $jsonConfig;
    private $bundles;
    private $bundlesForJson;
    
    public function Open($path, $name)
    {
        $json = Json::fromFile($path . "/" . $name . ".nrd");
        if ($json == []) return;

        $platform = MainModule::getProjects()->getPlatform($json['platform']);
        if (!$platform) return;
  
        $type = $platform->getProjectType();
        if (!$type) return;
        
        if ($platform instanceof JphpConsolePlatform || $platform instanceof JphpGuiPlatform)
        {
            $this->registerBundle(new JPHPCoreBundle());
            $this->registerBundle(new JPHPRuntimeBundle());
        }
        
        if ($platform instanceof JphpGuiPlatform)
        {
            $this->registerBundle(new JPHPJavaFXBundle());
        }
        
        $this->dir  = $path;
        $this->name = $name;
        $this->type = $type;
        $this->platform = $platform;
        $this->jsonConfig = $json;
        
        app()->getForm("project")->OpenProject($this);
        return true;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getDir()
    {
        return $this->dir;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getJson()
    {
        return Json::fromFile($this->getDir() . "/" . $this->getName() . ".nrd");
    }
    
    public function getPlatform()
    {
        return $this->platform;
    }
    
    public function build()
    {
        $form = app()->getForm("BuildType");
        $form->show();
        
        foreach ($this->platform->getAllBuildTypes() as $type)
        {
            $form->addItem($type, $this);
        }
    }
    
    public function registerBundle(bundleType $bundle)
    {
        if ($this->bundles[$bundle->getName()]) return;
        $this->bundles[$bundle->getName()] = $bundle;
        $this->bundlesForJson[] = $bundle->getName();
        
        $json = $this->getJson();
        $json['bundles'] = $this->bundlesForJson;
        Json::toFile($this->getDir() . "/" . $this->getName() . ".nrd", $json);
    }
    
    public function getBundles()
    {
        return $this->bundles;
    }
    
}
