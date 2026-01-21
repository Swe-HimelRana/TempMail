<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\ImapClient;

try {
    $count = ImapClient::fetchAllMessages();
    echo "Synced $count new messages.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
