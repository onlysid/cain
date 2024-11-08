<?php // Define all fields and their position in bitfields with the settings table of the database.

// Behaviour = Tablet entry behaviour
// Visibility = Hub visibility

class Field {
    public $name;
    public $dbName;
    public $behaviourLock;
    public $visibilityLock;

    public function __construct(string $name, string $dbName, bool $behaviourLock = false, bool $visibilityLock = false) {
        $this->name = $name;
        $this->dbName = $dbName;
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
    new Field("Patient ID", "patientId"),
    new Field("NHS Number", "nhsNumber"),
    new Field("First Name", "firstName"),
    new Field("Last Name", "lastName"),
    new Field("Date of Birth", "dob"),
    new Field("Patient Age", "patientAge"),
    new Field("Gender", "patientSex"),
    new Field("Hospital ID", "hospitalId"),
    new Field("Patient Location", "patientLocation"),
    new Field("Sample Collected", "sampleCollected"),
    new Field("Sample Received", "sampleReceived"),
    new Field("Comment 1", "reserve1"),
    new Field("Comment 2", "reserve2"),
];