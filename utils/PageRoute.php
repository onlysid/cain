<?php // PageRoute Class for creating link-to-view relationships
class PageRoute {
    public $view;
    public $showMenu;
    public $title;
    public $accessLevel;
    public $showFooter;

    public function __construct($view, $title = "Cain Medical", $showMenu = true, $accessLevel = CLINICIAN, $showFooter = true) {
        $this->view = $view;
        $this->title = $title;
        $this->showMenu = $showMenu;
        $this->accessLevel = $accessLevel;
        $this->showFooter = $showFooter;
    }
}
?>
