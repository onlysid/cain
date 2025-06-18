<?php
$logFile = __DIR__ . '/../simdata/simoutput.txt';
file_put_contents($logFile, '');
http_response_code(200);