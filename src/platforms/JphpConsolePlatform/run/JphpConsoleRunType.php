<?php
namespace platforms\JphpConsolePlatform\run;

use php\lang\Process;
use php\lib\fs;
use php\io\Stream;
use php\lang\Thread;
use app\modules\MainModule;
use php\io\File;
use php\lib\str;
use php\lib\arr;
use app\forms\project;
use ui\BuildLog;
use Types\RunType;

class JphpConsoleRunType extends RunType
{
    public function onRun(BuildLog $log, \utils\Project $project, $call = null)
    {
        foreach ($project->getBundles() as $jar)
        {
            foreach ($jar->getJarDependances() as $file)
            {
                $jars[] = fs::abs("./lib/" . $file . ".jar");
            }
        }
        
        $jars = str::join($jars, File::PATH_SEPARATOR);
        
        $classPaths = arr::toList([ 'src', 'src_generated' ], $jars);
        $args = [
            'java', '-cp', str::join($classPaths, File::PATH_SEPARATOR),
            '-XX:+UseG1GC', '-Xms128M', '-Xmx512m', '-Dfile.encoding=UTF-8', '-Djphp.trace=true',
            'org.develnext.jphp.ext.javafx.FXLauncher'
        ];
        $log->print("> java -cp ... org.develnext.jphp.ext.javafx.FXLauncher" , "green");
        $log->print("  --> " . $project->getDir(), "gray");
            
        $procces = new Process($args, $project->getDir(), MainModule::makeEnv()); 
        
        $p = $procces->start();
        
        $t = new Thread(function () use ($p, $log, $call, $project) {
            $p->getInput()->eachLine( function($line) use ($log) {
                uiLater(function () use ($line, $log) {
                    $color = "#fff";
                
                    if (strpos(" ". $line, '[ERROR]')){
                        $color = "red";
                    }
                    if (strpos(" ". $line, '[DEBUG]')){
                        $color = "gray";
                    }
                    if (strpos(" ". $line, '[INFO]'))
                    {
                        $color = "#4d66cc";
                    }
                    if (strpos(" ". $line, '[WARN]')){
                        $color = "#999900";
                    }
                    
                    
                    $log->print($line, $color);
                });
            });
            
            $p->getError()->eachLine( function($line)  use ($log) {
                uiLater(function () use ($line, $log) {
                    $log->print($line, "red");
                });
            });
            
            $exitValue = $p->getExitValue();
            uiLater(function () use ($log, $exitValue, $call, $project) {
                    $log->print("> exit code: " . $exitValue, "green");
                
                if ($call)
                    $call();
            });
        });
        $t->start();
    }
    
    public function onStop(\utils\Project $project, $call)
    {
        $pid = Stream::getContents($project->getDir() . "/application.pid");
        if ($pid)
        {
            $os = MainModule::getOS();
            if ($os == "windows") {
                $result = `taskkill /PID $pid /f`;
            } else {
                $result = `kill -9 $pid`;
            }
            if (!$result) {
                var_dump("Не удалось остановить приложение. pid : " . $pid);
            } else {
                 $call();
            }
        }
    }
    
    public function getJars()
    {
        $lib = fs::abs("./lib/");
        $dir = new File($lib);
        foreach ($dir->findFiles() as $one)
        {
            if ($one->isFile())
            {
                $libs[] = fs::pathNoExt($one) . ".jar";
            }
        }
        
        return $libs;
    }
}