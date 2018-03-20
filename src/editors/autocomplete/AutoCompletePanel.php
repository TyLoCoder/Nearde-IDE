<?php
namespace editors\autocomplete;

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
            Logger::info("AutoComplete key : " . $e->codeName);
            $this->update();
            switch ($e->codeName) {
                case 'Up':
                    $e->consume();
                    break;
                case 'Down':
                    $e->consume();
                    break;
                case 'Enter':
                    $e->consume();
                    $this->enter();
                    $this->update();
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
                    $this->line .= $e->codeName;
                    if ($this->line) Logger::info("AutoComplete curent line : " . $this->line);
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
        $this->window->show($this->textArea->form, $x + 5, $y);
    }
    
    public function update()
    {
        $this->hide();
        $arr = $this->listView->items->toArray();
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
    }
    
    private function enter()
    {
        UXApplication::runLater(function () {
            $this->hide();
            
            $item = $this->listView->selectedItems[0];
            if ($item == null) return;
            var_dump($item);
            
            uiLater([$this, 'show']);
        });
    }
}