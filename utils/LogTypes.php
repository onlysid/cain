<?php // Creating log types for txt based logging
class LogTypes {
    public $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function __toString(): string {
        return array_map('ucfirst', explode('-', $this->name));
    }
}

$logTypes = [
    new LogTypes('events'),
    new LogTypes('access'),
    new LogTypes('system'),
    new LogTypes('QC'),
    new LogTypes('API')
];
