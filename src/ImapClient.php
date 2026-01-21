<?php

namespace App;

use PhpImap\Mailbox;

class ImapClient
{
    public static function fetchAllMessages()
    {
        $accounts = Config::get('accounts', []);
        $fetchedCount = 0;

        foreach ($accounts as $account) {
            $host = $account['host'];
            $user = $account['email'];
            $password = $account['password'];
            $port = $account['port'];
            $ssl = $account['encryption'] === 'ssl' ? '/ssl' : ($account['encryption'] === 'tls' ? '/tls' : '');
            
            $foldersToCheck = ['INBOX'];
            
            // Try to add common spam folders
            $potentialSpam = ['[Gmail]/Spam', 'Junk', 'Spam', 'Junk E-mail'];
            
            foreach ($potentialSpam as $spamFolder) {
                $foldersToCheck[] = $spamFolder;
            }

            foreach ($foldersToCheck as $folderName) {
                // Determine full path
                $path = "{{$host}:{$port}/imap{$ssl}}{$folderName}";

                // Ensure attachments directory exists
                $attachmentsDir = __DIR__ . '/../data/attachments';
                if (!is_dir($attachmentsDir)) {
                    mkdir($attachmentsDir, 0777, true);
                }

                try {
                    $mailbox = new Mailbox(
                        $path,
                        $user,
                        $password,
                        $attachmentsDir
                    );

                    // Get all emails
                    try {
                        $mailIds = $mailbox->searchMailbox('ALL');
                    } catch (\Exception $e) {
                         // Likely folder doesn't exist
                        continue;
                    }

                    if(!$mailIds) continue;

                    // Optimization: Process only the latest 50 messages to avoid timeouts
                    rsort($mailIds); // Newest first
                    $mailIds = array_slice($mailIds, 0, 50);

                    foreach ($mailIds as $mailId) {
                        $mail = $mailbox->getMail($mailId);
                        
                        $recipient = null;
                        if (!empty($mail->to)) {
                            foreach ($mail->to as $address => $name) {
                                foreach (Config::getAllDomains() as $domain) {
                                    if (str_contains($address, $domain)) {
                                        $recipient = $address;
                                        break 2;
                                    }
                                }
                            }
                        }
                        
                        if (!$recipient) {
                             $recipient = array_key_first($mail->to);
                        }

                        $saved = Database::saveEmail([
                            'account_email' => $user,
                            'message_id' => $mail->messageId ?? md5($mail->date . $mail->subject),
                            'sender' => $mail->fromName ? "{$mail->fromName} <{$mail->fromAddress}>" : $mail->fromAddress,
                            'recipient' => $recipient,
                            'subject' => $mail->subject,
                            'body_html' => $mail->textHtml,
                            'body_text' => $mail->textPlain,
                            'received_at' => $mail->date
                        ]);

                        if ($saved) {
                            $fetchedCount++;
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        return $fetchedCount;
    }

    public static function testConnections()
    {
        $accounts = Config::get('accounts', []);
        $results = [];

        foreach ($accounts as $account) {
            $host = $account['host'];
            $user = $account['email'];
            $password = $account['password'];
            $port = $account['port'];
            $ssl = $account['encryption'] === 'ssl' ? '/ssl' : ($account['encryption'] === 'tls' ? '/tls' : '');
            
            // Just check INBOX for connectivity test
            $path = "{{$host}:{$port}/imap{$ssl}}INBOX";

            try {
                // Ensure attachments directory exists
                $attachmentsDir = __DIR__ . '/../data/attachments';
                if (!is_dir($attachmentsDir)) {
                    mkdir($attachmentsDir, 0777, true);
                }

                $mailbox = new Mailbox(
                    $path,
                    $user,
                    $password,
                    $attachmentsDir
                );
                
                // Try to get mailbox info to verify connection
                $check = $mailbox->checkMailbox();
                $results[] = [
                    'host' => $host,
                    'email' => $user,
                    'status' => 'OK',
                    'details' => "Connected successfully. Messages: " . ($check->Nmsgs ?? 0)
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'host' => $host,
                    'email' => $user,
                    'status' => 'Error',
                    'error' => $e->getMessage()
                ];
            }
        }
        return $results;
    }
}
