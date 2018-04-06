<?php
namespace platforms\JphpConsolePlatform\build;

use php\lang\Process;
use Types\BuildType;
use php\lang\Thread;
use php\io\Stream;
use php\lib\fs;
use php\io\File;
use app\modules\MainModule;
use utils\Project;

class JphpConsoleBuildOneJarType extends BuildType
{
    public function getName()
    {
        return "JAR Приложение.";
    }
    
    public function getId()
    {
        return __CLASS__;
    }
    
    public function getIcon()
    {
        return "res://.data/img/jarFile32.png";
    }
    
    public function getDescription(){
        return "Приложение для Windows/Linux/MacOS (JRE 1.8+)";
    }
    
    public function build(\utils\Project $project)
    {
        $ui = app()->getForm("BuildUI");
        
        $ui->show();
        
        $byte_p = MainModule::toByteCode(new File($project->getDir()));
        
        fs::makeDir($project->getDir() . "/build/dist/lib/");
        fs::makeDir($project->getDir() . "/build/dist/gen/");
        fs::makeFile($project->getDir() . "/build.xml");
        
        fs::makeFile($project->getDir() . "/build/dist/gen/META-INF/services/php.runtime.ext.support.Extension");
        Stream::putContents($project->getDir() . "/build/dist/gen/META-INF/services/php.runtime.ext.support.Extension", "org.develnext.jphp.json.JsonExtension\n\norg.develnext.jphp.ext.xml.XmlExtension\n\norg.develnext.jphp.ext.javafx.JavaFXExtension\n\norg.develnext.jphp.ext.gui.desktop.GuiDesktopExtension\n\norg.develnext.jphp.zend.ext.ZendExtension\n\norg.develnext.jphp.parser.ParserExtension\n");
        
        $env = MainModule::makeEnv();
        
        $build = Stream::getContents("res://platforms/JphpConsolePlatform/build/build.xml");
        
        $build = str_replace("%NAME%", $project->getName(), $build);
        $build = str_replace("%JRE%", $env['JAVA_HOME'], $build);
        
        foreach ($project->getBundles() as $jar)
        {
            foreach ($jar->getJarDependances() as $file)
            {
                $jars[] = fs::abs("./lib/" . $file . ".jar");
            }
        }
        
        var_dump($jars);
        
        foreach ($jars as $file)
        {
            $f = new File($project->getDir() . "/build/dist/lib/" . fs::name($file));
            $zips .= '<zipfileset src=\'${dist}/lib/'.fs::name($file)."' excludes='.debug/** ' /> \n";
            $ui->print(":use-jar " . fs::name($file), "gray");
            fs::makeFile($project->getDir() . "/build/dist/lib/" . fs::name($file));
            fs::copy($file, $f);
        }
        
        $build = str_replace("%ZIPFILESET%", $zips, $build);
        
        Stream::putContents($project->getDir() . "/build.xml", $build);
        
        if (MainModule::getOS() == "windows")
        {
          $args = [
              'cmd.exe',
              '/c',
              $env['ANT_HOME'] . '/bin/ant.bat',
              'onejar'
          ];  
        } else {
            $args = [
              'ant',
              'onejar'
          ];  
        }
        
        $procces = new Process($args, $project->getDir()); 
        $p = $procces->start();
        
        new Thread(function () use ($p, $ui, $call, $project) {
            $p->getInput()->eachLine( function($line) use ($ui) {
                uiLater(function () use ($line, $ui) {
                    if ($line == "BUILD SUCCESSFUL")
                    {
                        $ui->print($line, 'green');
                    } else $ui->print($line);
                });
            });
            
            $p->getError()->eachLine( function($line)  use ($ui) {
                uiLater(function () use ($line, $ui) {
                    $ui->print($line, "red");
                });
            });
            
            $exitValue = $p->getExitValue();
            uiLater(function () use ($ui, $exitValue, $call, $project) {
                if ($exitValue == 0)
                {
                    $form = app()->getForm("BuildEnd");
                    $form->open(fs::abs($project->getDir() . "/build/dist/"), function () use ($project) {
                        open(fs::abs($project->getDir() . "/build/dist/" . $project->getName() . ".jar"));
                    });
                    $ui->hide();
                    $form->show();
                } else $ui->print("> exit code: " . $exitValue);
                
                if ($call)
                    $call();
            });
        })->start();
    }
}