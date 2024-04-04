<?php // Define all fields and their position in bitfields with the settings table of the database.

class Field {
    public $name;
    public $behaviourLock;
    public $visibilityLock;

    public function __construct(string $name, bool $behaviourLock = false, bool $visibilityLock = false) {
        $this->name = $name;
        $this->behaviourLock = $behaviourLock;
        $this->visibilityLock = $visibilityLock;
    }
}

/* 
We must display a bitfield representation of the varchar number stored in the database.
arrSize = How many "settings" are stored in the integer?
chunkSize = How many "options" are there per setting?
settingValue = The value we want to parse.
Returns an array of integers (ie, if there are 4 options for 32 settings fields, it will be an array of 2-bit numbers of length 32).
*/
function getSettingsBitmap($arrSize, $chunkSize, $settingValue) {
    // Convert field info into array of bits according to the size of the dataFields array
    $bitmap = decbin(intval($settingValue));

    // Interpret chunksize by however many bits we need to represent that spread of data
    $bitsPerItem = ceil(log($chunkSize, 2));
    
    // Pad the binary representation with leading zeros to ensure it has the same number of digits as the dataFields array
    $paddedBinary = str_pad($bitmap, $arrSize * $bitsPerItem, '0', STR_PAD_LEFT);
    
    // Convert the padded binary string into an array of integers
    $bitsArray = array_map('intval', str_split($paddedBinary, $bitsPerItem));

    // Convert each 2-bit chunk into its decimal equivalent
    $bitmap = array_map(function ($chunk) {
        return bindec($chunk);
    }, $bitsArray);
    
    // Reverse the array to match the expected output format
    return array_reverse($bitmap);
}

/*
Converts that array of integers back into a bitfield (so that posting the results of changing these settings is meaningful).
We need the chunkSize again as this isn't inherently obvious from the bitmap array itself. It can sometimes be deduced but not always.
*/
function convertBitmapArrayToInt($bitmapArray, $chunkSize) {
    // Initialize the result
    $result = 0;

    // Interpret chunksize by however many bits we need to represent that spread of data
    $bitsPerItem = ceil(log($chunkSize, 2));

    // Iterate over the bitmap array in reverse
    $bitmapArray = array_reverse($bitmapArray);

    foreach ($bitmapArray as $bit) {
        // Shift the result left by the number of bits in the chunk
        $result <<= $bitsPerItem;
        // Add the current bit to the result
        $result |= $bit;
    }

    return $result;
}

// Fields Info Settings Subset
$hospitalInfoKeys = ['field_behaviour', 'field_visibility'];
$fieldInfo = array_intersect_key($settings, array_flip($hospitalInfoKeys));

// Define all fields
$dataFields = [
    new Field("Patient ID"),
    new Field("NHS Number"),
    new Field("First Name"),
    new Field("Last Name"),
    new Field("Date of Birth"),
    new Field("Patient Age", true),
    new Field("Gender"),
    new Field("Result", true, true),
    new Field("Hospital ID"),
    new Field("Site ID"),
    new Field("Clinic ID"),
    new Field("Patient Location"),
    new Field("Sample ID"),
    new Field("Sample Collected"),
    new Field("Sample Run", true),
    new Field("Assay Name", true, true),
    new Field("Lot Number", true),
    new Field("Test ID", true),
    new Field("Test Purpose", true),
    new Field("Test Complete Time", true),
    new Field("Operator ID", true),
    new Field("AM Serial No", true),
    new Field("Record Status", true),
];