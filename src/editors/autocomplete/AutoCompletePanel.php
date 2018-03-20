<?php
namespace editors\autocomplete;

use script\FileChooserScript;
use php\util\Regex;
use php\lib\arr;
use php\lib\str;
use php\framework\Logger;
use php\gui\UXApplication;
use php\gui\event\UXKeyEvent;
use php\gui\UXListView;
use php\gui\UXPopupWindow;
use php\gui\designer\UXAbstractCodeArea;

class AutoCompletePanel 
{
    /**
     * @var UXAbstractCodeArea
     */
    private $textArea;
    
    /**
     * @var UXPopupWindow
     */
    private $window;
    
    /**
     * @var UXListView
     */
    private $listView;
    
    private $line;
    
    private $items;
    
    public function __construct(UXAbstractCodeArea $textArea)
    {
        $this->textArea = $textArea;
        $this->makeUI();
        $this->init();
    }
    
    private function init()
    {
        $this->textArea->observer('focused')->addListener(function ($old, $new) {
            if (!$new) {
                $this->hide();
            }
        });
        
        $this->textArea->on('mouseDown', function () {
            $this->hide();
        });
        
        $this->textArea->on('keyDown', function (UXKeyEvent $e) {
            switch ($e->codeName) {
                case 'Up':
                    $this->up();
                    break;
                case 'Down':
                    $this->down();
                    break;
                case 'Enter':
                    $e->consume();
                    $this->enter();
                    break;
                case 'Left':
                case 'Right':
                    $this->hide();
                    break;
                case 'Esc':
                case 'Backspace':
                case 'Space':
                    $this->hide();
                    $e->consume();
                    $this->line = null;
                    break;
                default:
                    if (str::length($e->codeName) == 1)
                    {
                        $this->line .= strtolower($e->codeName);
                        $this->update();
                    }
                    break;
            }
        });
    }
    
    public function hide()
    {
        $this->window->hide();
    }
    
    public function show()
    {
        $caretBounds = $this->textArea->caretBounds;
        list($x, $y) = [$caretBounds['x'], $caretBounds['y']];
        $x += $caretBounds['width'];
        $y += $caretBounds['height'];
        $this->window->show($this->textArea->form, $x, $y);
    }
    
    public function update()
    {
        $this->hide();
        if (!$this->line) return;
        $arr = $this->items;
        $arr = array_map(function ($val) {
            if (Regex::match($this->line, $val) || $this->line == $val) {
                return $val;
            }
        }, $arr);
        $arr = array_filter($arr);
        if (!$arr) return;
        $this->listView->items->clear();
        $this->listView->items->addAll($arr);
        $this->show();
    }
    
    private function makeUI()
    {
        $this->window = new UXPopupWindow();
        $this->listView = new UXListView();
        $this->listView->maxHeight = 200;
        $this->listView->maxWidth = 400;
        $this->listView->focusTraversable = false;
        
        $this->window->add($this->listView);
    }
    
    public function add(string $i)
    {
        $this->listView->items->add($i);
        $this->items[] = $i;
    }
    
    private function enter()
    {
        if ($this->line)
        UXApplication::runLater(function () {
            $this->hide();
            $item = $this->listView->selectedItems[0];
            if ($item == null) return;
            
            $c_p = $this->textArea->caretPosition;
            $l_p = str::length($this->line);
            
            $p = $c_p - $l_p + 4;
            $this->textArea->deleteText($p, $c_p);
            $this->textArea->insertToCaret($item);
            $this->line = null;
        });
    }
    
    private function up()
    {
        UXApplication::runLater(function () {
            $this->listView->selectedIndex -= 1;
            if ($this->listView->selectedIndex == -1) {
                $this->listView->selectedIndex = $this->listView->items->count - 1;
            }
        });
        return true;
    }
    
    private function down()
    {
        UXApplication::runLater(function () {
            $this->listView->selectedIndex += 1;
            if ($this->listView->selectedIndex == -1) {
                $this->listView->selectedIndex = 0;
            }
        });
        return true;
    }
}