<?php // PageRoute Class for creating link-to-view relationships
class PageRoute {
    public $view;
    public $showMenu;
    public $title;
    public $accessLevel;

    public function __construct($view, $title = "Cain Medical", $showMenu = true, $accessLevel = CLINICIAN) {
        $this->view = $view;
        $this->title = $title;
        $this->showMenu = $showMenu;
        $this->accessLevel = $accessLevel;
    }
}
?>
