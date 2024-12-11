<?php
class MenuItem {
    public $pageRoute;
    public $title;
    public $icon;

    public function __construct($pageRoute, $title = null, $icon = null) {
        $this->pageRoute = $pageRoute;
        $this->title = $title;
        $this->icon = $icon;
    }
}
?>
