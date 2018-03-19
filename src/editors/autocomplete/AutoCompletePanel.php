<?php
namespace editors\autocomplete;

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
    
    public function __construct(UXAbstractCodeArea $textArea)
    {
        $this->textArea = $textArea;
        $this->init();
    }
    
    private function init()
    {
        $this->window = new UXPopupWindow();
        $this->listView = new UXListView();
        $this->window->add($this->listView);
        
        $this->textArea->observer('focused')->addListener(function ($old, $new) {
            if (!$new) {
                $this->hide();
            }
        });
        
        $this->textArea->on('mouseDown', function () {
            $this->update();
        });
        
        $this->textArea->on('keyDown', function (UXKeyEvent $e) {
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
                    $this->update();
                    break;
                case 'Left':
                case 'Right':
                    $this->hide();
                    break;
                case 'Esc':
                    $this->hide();
                    $e->consume();
                    break;
                default:
                    if ($e->controlDown && $e->codeName == 'Space') {
                        $e->consume();
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
        $this->show();
    }
}