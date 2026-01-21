<?php

// Prevent HTML errors from breaking JSON response
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Handle fatal errors gracefully
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (ob_get_length()) ob_clean(); // Clear corrupted buffer
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Server Error: ' . $error['message']]);
        exit;
    }
});

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config;
use App\Database;
use App\ImapClient;

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Simple PIN protection check (except for login)
session_start();
if ($action !== 'login' && !isset($_SESSION['authenticated'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Release session lock to prevent blocking other requests (like reading emails while syncing)
session_write_close();

try {
    switch ($action) {
        case 'login':
            // Login needs to write to session, so we need to restart it
            session_start();
            $input = json_decode(file_get_contents('php://input'), true);
            $pin = Config::get('pin');
            if (($input['pin'] ?? '') === (string)$pin) {
                $_SESSION['authenticated'] = true;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid PIN']);
            }
            break;

        case 'logout':
            session_destroy();
            echo json_encode(['success' => true]);
            break;

        case 'domains':
            echo json_encode(['domains' => array_values(Config::getAllDomains())]);
            break;

        case 'sync':
            $count = ImapClient::fetchAllMessages();
            echo json_encode(['success' => true, 'new_messages' => $count]);
            break;

        case 'check_imap':
            $results = ImapClient::testConnections();
            echo json_encode(['results' => $results]);
            break;

        case 'messages':
            $email = $_GET['email'] ?? '';
            if (!$email) {
                echo json_encode(['messages' => []]);
                break;
            }
            $messages = Database::getMessages($email);
            echo json_encode(['messages' => $messages]);
            break;

        case 'accounts':
            $search = $_GET['search'] ?? '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $result = Database::getAccounts($search, $limit, $offset);
            echo json_encode($result);
            break;

        case 'message':
            $id = $_GET['id'] ?? '';
            if (!$id) {
                throw new Exception("ID required");
            }
            $message = Database::getMessage($id);
            echo json_encode(['message' => $message]);
            break;
            
        case 'delete_message':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? '';
            if (!$id) {
                throw new Exception("ID required");
            }
            Database::deleteMessage($id);
            echo json_encode(['success' => true]);
            break;

        case 'delete_account':
            $input = json_decode(file_get_contents('php://input'), true);
            $email = $input['email'] ?? '';
            if (!$email) {
                throw new Exception("Email required");
            }
            Database::deleteAccount($email);
            echo json_encode(['success' => true]);
            break;

        case 'domains':
            echo json_encode(['domains' => Config::getAllDomains()]);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
