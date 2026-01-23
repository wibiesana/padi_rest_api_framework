<?php

namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    /**
     * Send an email
     */
    public static function send(string $to, string $subject, string $body, array $attachments = []): bool
    {
        $config = require dirname(__DIR__) . '/app/Config/mail.php';
        $mail = new PHPMailer(true);

        try {
            // Server settings
            if (($config['driver'] ?? 'smtp') === 'smtp') {
                $mail->isSMTP();
                $mail->Host       = $config['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $config['username'];
                $mail->Password   = $config['password'];
                $mail->SMTPSecure = $config['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = $config['port'];
            }

            // Recipients
            $mail->setFrom($config['from_address'], $config['from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            // Attachments
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            if (Debug::isEnabled()) {
                Debug::log("Email failed to send: " . $mail->ErrorInfo, 'error');
            }
            Logger::error("Email failed to send", ['error' => $mail->ErrorInfo, 'to' => $to]);
            return false;
        }
    }
}
