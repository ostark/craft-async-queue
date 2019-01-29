<?php
/**
 * This is a dummy script to emulate ./craft queue/run
 */

require __DIR__ . '/bootstrap.php';

$content = json_encode(['$argv' => $argv, 'timestamp' => time(), 'date' => date('c')]);

echo 'Start' . PHP_EOL;
file_put_contents(TEST_FILE, $content);
sleep(1);
echo 'Stop' . PHP_EOL;

if (in_array('--error', $argv)) {
    exit(99);
}

exit(0);


