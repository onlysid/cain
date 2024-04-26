<?php // Creating age groups for demographic filters
class AgeGroup {
    public $min;
    public $max;

    public function __construct($min, $max = null) {
        $this->min = $min;
        $this->max = $max;
    }

    public function __toString(): string {
        return $this->min . ($this->max ? ("-" . $this->max) : "+");
    }
}

$ageGroups = [
    new AgeGroup(0, 4),
    new AgeGroup(5, 11),
    new AgeGroup(12, 17),
    new AgeGroup(18, 25),
    new AgeGroup(26, 34),
    new AgeGroup(35, 49),
    new AgeGroup(50, 69),
    new AgeGroup(70, 79),
    new AgeGroup(80, 89),
    new AgeGroup(90),
]
?>
