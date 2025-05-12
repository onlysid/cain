<?php
class Software {
    public $dbItem;
    public $scripts;
    public $changelogLink;

    public function __construct($dbItem, $scripts = null, $changelogLink = null) {
        // DB Object for the software
        $this->dbItem = $dbItem;
        // Optionally, provide a changelog.
        $this->changelogLink = $changelogLink;
        // Optionally, an object containing {"action title"->"action"}.
        $this->scripts = $scripts;
    }

    public function getVersion() {
        global $cainDB;
        return $cainDB->selectAll("SELECT `value` FROM `versions` WHERE `software` = :dbItem;", [":dbItem" => $this->dbItem]);
    }

    public function getTitle() {
        global $cainDB;
        return $cainDB->select("SELECT `name` FROM `software` WHERE id = :dbItem;", [":dbItem" => $this->dbItem])['name'];
    }
}

// Define all items
$software = [
    new Software(1, ["Attempt Database Repair" => "reset-db-version"], "/changelog"),
    // DMS Hub version (should get dynamically somehow)
    // new Software(2),
    // Tablet/Instrument Versions if necessary (must be valid in the DB)
    // new Software(3),
    // new Software(4),
];?>
