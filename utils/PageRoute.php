<?php // PageRoute Class for creating link-to-view relationships
class PageRoute {
    public $view;
    public $showMenu;
    public $title;

    public function __construct($view, $title = "Cain Medical", $showMenu = true) {
        $this->view = $view;
        $this->title = $title;
        $this->showMenu = $showMenu;
    }
}
?>
