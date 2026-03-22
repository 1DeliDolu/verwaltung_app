<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class InternalMail
{
    public static function create(array $message, array $recipients, array $attachments): int
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'INSERT INTO internal_mails (
                sender_id,
                sender_name,
                sender_email,
                subject,
                body
            ) VALUES (
                :sender_id,
                :sender_name,
                :sender_email,
                :subject,
                :body
            )'
        );
        $statement->execute([
            'sender_id' => $message['sender_id'],
            'sender_name' => $message['sender_name'],
            'sender_email' => $message['sender_email'],
            'subject' => $message['subject'],
            'body' => $message['body'],
        ]);

        $mailId = (int) $pdo->lastInsertId();

        $recipientStatement = $pdo->prepare(
            'INSERT INTO internal_mail_recipients (
                mail_id,
                recipient_user_id,
                recipient_name,
                recipient_email
            ) VALUES (
                :mail_id,
                :recipient_user_id,
                :recipient_name,
                :recipient_email
            )'
        );

        foreach ($recipients as $recipient) {
            $recipientStatement->execute([
                'mail_id' => $mailId,
                'recipient_user_id' => $recipient['id'],
                'recipient_name' => $recipient['name'],
                'recipient_email' => $recipient['email'],
            ]);
        }

        if ($attachments !== []) {
            $attachmentStatement = $pdo->prepare(
                'INSERT INTO internal_mail_attachments (
                    mail_id,
                    original_name,
                    mime_type,
                    file_size,
                    file_content
                ) VALUES (
                    :mail_id,
                    :original_name,
                    :mime_type,
                    :file_size,
                    :file_content
                )'
            );

            foreach ($attachments as $attachment) {
                $attachmentStatement->bindValue(':mail_id', $mailId, PDO::PARAM_INT);
                $attachmentStatement->bindValue(':original_name', $attachment['name']);
                $attachmentStatement->bindValue(':mime_type', $attachment['mime']);
                $attachmentStatement->bindValue(':file_size', strlen((string) $attachment['content']), PDO::PARAM_INT);
                $attachmentStatement->bindValue(':file_content', $attachment['content'], PDO::PARAM_LOB);
                $attachmentStatement->execute();
            }
        }

        $pdo->commit();

        return $mailId;
    }

    public static function mailboxForUser(int $userId, array $filters = []): array
    {
        $inbox = self::fetchMessages('inbox', $userId, $filters);
        $sent = self::fetchMessages('sent', $userId, $filters);

        return ['inbox' => $inbox, 'sent' => $sent];
    }

    public static function inboxCountForUser(int $userId): int
    {
        $statement = self::pdo()->prepare(
            'SELECT COUNT(DISTINCT internal_mails.id)
             FROM internal_mails
             INNER JOIN internal_mail_recipients
                 ON internal_mail_recipients.mail_id = internal_mails.id
             WHERE internal_mail_recipients.recipient_user_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public static function attachmentForUser(int $userId, int $mailId, int $attachmentId): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT internal_mail_attachments.id,
                    internal_mail_attachments.original_name,
                    internal_mail_attachments.mime_type,
                    internal_mail_attachments.file_content
             FROM internal_mail_attachments
             INNER JOIN internal_mails ON internal_mails.id = internal_mail_attachments.mail_id
             LEFT JOIN internal_mail_recipients
                 ON internal_mail_recipients.mail_id = internal_mails.id
             WHERE internal_mail_attachments.id = :attachment_id
               AND internal_mail_attachments.mail_id = :mail_id
               AND (
                   internal_mails.sender_id = :sender_user_id
                   OR internal_mail_recipients.recipient_user_id = :recipient_user_id
               )
             LIMIT 1'
        );
        $statement->execute([
            'attachment_id' => $attachmentId,
            'mail_id' => $mailId,
            'sender_user_id' => $userId,
            'recipient_user_id' => $userId,
        ]);
        $attachment = $statement->fetch();

        return $attachment === false ? null : $attachment;
    }

    private static function fetchMessages(string $folder, int $userId, array $filters): array
    {
        $scope = (string) ($filters['scope'] ?? 'all');
        $term = trim((string) ($filters['term'] ?? ''));
        $params = ['user_id' => $userId];

        $query = 'SELECT internal_mails.id AS message_id,
                         internal_mails.subject,
                         internal_mails.body,
                         internal_mails.created_at,
                         internal_mails.sender_name,
                         internal_mails.sender_email,
                         GROUP_CONCAT(DISTINCT recipients.recipient_email ORDER BY recipients.recipient_email SEPARATOR \', \') AS recipient_list
                  FROM internal_mails
                  INNER JOIN internal_mail_recipients AS recipients
                      ON recipients.mail_id = internal_mails.id ';

        if ($folder === 'inbox') {
            $query .= 'INNER JOIN internal_mail_recipients AS viewer_recipient
                           ON viewer_recipient.mail_id = internal_mails.id
                      WHERE viewer_recipient.recipient_user_id = :user_id ';
        } else {
            $query .= 'WHERE internal_mails.sender_id = :user_id ';
        }

        if ($term !== '') {
            $search = '%' . $term . '%';

            switch ($scope) {
                case 'sender':
                    $query .= 'AND (internal_mails.sender_name LIKE :sender_name_term OR internal_mails.sender_email LIKE :sender_email_term) ';
                    $params['sender_name_term'] = $search;
                    $params['sender_email_term'] = $search;
                    break;
                case 'recipient':
                    $query .= 'AND EXISTS (
                        SELECT 1
                        FROM internal_mail_recipients AS search_recipient
                        WHERE search_recipient.mail_id = internal_mails.id
                          AND (search_recipient.recipient_name LIKE :recipient_name_term OR search_recipient.recipient_email LIKE :recipient_email_term)
                    ) ';
                    $params['recipient_name_term'] = $search;
                    $params['recipient_email_term'] = $search;
                    break;
                case 'content':
                    $query .= 'AND (internal_mails.subject LIKE :subject_term OR internal_mails.body LIKE :body_term) ';
                    $params['subject_term'] = $search;
                    $params['body_term'] = $search;
                    break;
                default:
                    $query .= 'AND (
                        internal_mails.sender_name LIKE :all_sender_name_term
                        OR internal_mails.sender_email LIKE :all_sender_email_term
                        OR internal_mails.subject LIKE :all_subject_term
                        OR internal_mails.body LIKE :all_body_term
                        OR EXISTS (
                            SELECT 1
                            FROM internal_mail_recipients AS search_recipient
                            WHERE search_recipient.mail_id = internal_mails.id
                              AND (search_recipient.recipient_name LIKE :all_recipient_name_term OR search_recipient.recipient_email LIKE :all_recipient_email_term)
                        )
                    ) ';
                    $params['all_sender_name_term'] = $search;
                    $params['all_sender_email_term'] = $search;
                    $params['all_subject_term'] = $search;
                    $params['all_body_term'] = $search;
                    $params['all_recipient_name_term'] = $search;
                    $params['all_recipient_email_term'] = $search;
                    break;
            }
        }

        $query .= 'GROUP BY internal_mails.id, internal_mails.subject, internal_mails.body, internal_mails.created_at, internal_mails.sender_name, internal_mails.sender_email
                   ORDER BY internal_mails.created_at DESC';

        $statement = self::pdo()->prepare($query);
        $statement->execute($params);
        $messages = $statement->fetchAll() ?: [];

        if ($messages === []) {
            return [];
        }

        $attachments = self::attachmentsForMailIds(array_map(
            static fn (array $message): int => (int) $message['message_id'],
            $messages
        ));

        return array_map(
            static function (array $message) use ($attachments): array {
                return [
                    'message_id' => (int) $message['message_id'],
                    'subject' => (string) $message['subject'],
                    'from' => (string) $message['sender_email'],
                    'to' => $message['recipient_list'] === null ? [] : array_map('trim', explode(',', (string) $message['recipient_list'])),
                    'body' => (string) $message['body'],
                    'html_body' => null,
                    'created_at' => (string) $message['created_at'],
                    'attachments' => $attachments[(int) $message['message_id']] ?? [],
                ];
            },
            $messages
        );
    }

    private static function attachmentsForMailIds(array $mailIds): array
    {
        if ($mailIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($mailIds), '?'));
        $statement = self::pdo()->prepare(
            "SELECT id, mail_id, original_name, mime_type
             FROM internal_mail_attachments
             WHERE mail_id IN ($placeholders)
             ORDER BY id"
        );
        $statement->execute($mailIds);
        $attachments = [];

        foreach ($statement->fetchAll() ?: [] as $attachment) {
            $attachments[(int) $attachment['mail_id']][] = [
                'id' => (int) $attachment['id'],
                'name' => (string) $attachment['original_name'],
                'mime' => (string) $attachment['mime_type'],
                'download_url' => '/mail/attachments/' . (int) $attachment['mail_id'] . '/' . (int) $attachment['id'],
            ];
        }

        return $attachments;
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
