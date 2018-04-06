<?php
namespace editors;
use editors\autocomplete\AutoCompletePanel;
use ide\autocomplete\AutoComplete;
use ide\autocomplete\ui\AutoCompletePane;
use editors\autocomplete\CompletePane;
use develnext\lexer\inspector\PHPInspector;
use php\gui\designer\UXTextCodeArea;
use php\gui\designer\UXCodeAreaScrollPane;
use php\gui\designer\UXJavaScriptCodeArea;
use php\gui\designer\UXCssCodeArea;
use php\gui\designer\UXPhpCodeArea;
use gui;
use std;
use app;
class CodeEditor
{
    private $editor;
    private $editor_whith_scrol;
    private $file;
    private $isCode;
    private $auto;
    
    public function  __construct(File $file)
    {
        $this->file   = $file;
        $this->isCode = true;
        switch (fs::ext($file))
        {
            case ("php"):
                $editor = new UXPhpCodeArea();
            break;
            
            case ("css"):
            case ("fxcss"):
                $editor = new UXCssCodeArea();
            break;
            
            case ("js"):
                $editor = new UXJavaScriptCodeArea();
            break;
            
            case ("txt"):
            case ("ini"):
            case ("conf"):
            case ("pid"):
            case ("xml"):
                $editor = new UXTextCodeArea();
            break;
            
            default: 
                $this->isCode = false;
        }
        
        if ($this->isCode)
        {
            $editor->text = Stream::getContents($file);
            $editor->setStylesheet(".theme/style-editor.css");
            
            $auto = new AutoCompletePanel($editor);
            $auto->add("new");
            $auto->add("if");
            $auto->add("else");
            $auto->add("elseif");
            
            $editor->on('keyUp', function (UXKeyEvent $e) {
                $this->save();
            });
            
            $this->editor = $editor;
            
            $scrol = new UXCodeAreaScrollPane($this->editor);
            $scrol->anchors = [
                "top" => 1,
                "bottom" => 1,
                "left" => 1,
                "right" => 1
            ];
            $this->editor_whith_scrol = $scrol;
        }
    }
    
    public function makeUI()
    {
        if ($this->isCode)
            return $this->editor_whith_scrol;
    }
    
    public function getEditor()
    {
        if ($this->isCode)
            return $this->editor;
    }
    
    public function save()
    {
        if ($this->isCode)
            Stream::putContents($this->file, $this->editor->text);
    }
}