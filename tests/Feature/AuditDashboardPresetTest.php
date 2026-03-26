<?php

declare(strict_types=1);

final class AuditDashboardPresetTest extends TestCase
{
    public function testAdminMaySaveAuditPresetAndSeeItListed(): void
    {
        $this->withDatabaseTransaction(function (\PDO $pdo): void {
            $admin = $this->userByEmail('admin@verwaltung.local');
            $dashboard = $this->dispatchApp('GET', '/audit?source=mail&outcome=failure&date_from=2030-01-01&date_to=2030-01-31', [
                'auth_user' => $admin,
            ]);

            $token = (string) ($dashboard['session']['_csrf_token'] ?? '');

            $result = $this->dispatchApp('POST', '/audit/presets', $dashboard['session'], [
                '_token' => $token,
                'return_to' => '/audit?source=mail&outcome=failure&date_from=2030-01-01&date_to=2030-01-31',
                'name' => 'Mail Fehler Januar',
                'source' => 'mail',
                'search' => '',
                'outcome' => 'failure',
                'date_from' => '2030-01-01',
                'date_to' => '2030-01-31',
            ]);

            $this->assertSame('/audit?source=mail&outcome=failure&date_from=2030-01-01&date_to=2030-01-31', $result['redirect_to']);
            $this->assertSame('Audit-Preset wurde gespeichert.', $result['session']['_flash']['success'] ?? null);

            $statement = $pdo->prepare(
                'SELECT name, source, outcome, date_from, date_to
                 FROM audit_filter_presets
                 WHERE user_id = :user_id
                   AND name = :name'
            );
            $statement->execute([
                'user_id' => (int) $admin['id'],
                'name' => 'Mail Fehler Januar',
            ]);
            $preset = $statement->fetch() ?: [];

            $this->assertSame('Mail Fehler Januar', $preset['name'] ?? null);
            $this->assertSame('mail', $preset['source'] ?? null);
            $this->assertSame('failure', $preset['outcome'] ?? null);
            $this->assertSame('2030-01-01', $preset['date_from'] ?? null);
            $this->assertSame('2030-01-31', $preset['date_to'] ?? null);

            $listed = $this->dispatchApp(
                'GET',
                '/audit?source=mail&outcome=failure&date_from=2030-01-01&date_to=2030-01-31',
                $result['session']
            );

            $this->assertSame(200, $listed['status']);
            $this->assertStringContains('Audit-Preset wurde gespeichert.', $listed['content']);
            $this->assertStringContains('Mail Fehler Januar', $listed['content']);
            $this->assertStringContains(
                '/audit?source=mail&amp;outcome=failure&amp;date_from=2030-01-01&amp;date_to=2030-01-31',
                $listed['content']
            );
        });
    }

    public function testAdminMayDeleteOwnAuditPreset(): void
    {
        $this->withDatabaseTransaction(function (\PDO $pdo): void {
            $admin = $this->userByEmail('admin@verwaltung.local');

            $statement = $pdo->prepare(
                'INSERT INTO audit_filter_presets (user_id, name, source, search, outcome, date_from, date_to)
                 VALUES (:user_id, :name, :source, :search, :outcome, :date_from, :date_to)'
            );
            $statement->execute([
                'user_id' => (int) $admin['id'],
                'name' => 'Task Review',
                'source' => 'task',
                'search' => null,
                'outcome' => 'success',
                'date_from' => null,
                'date_to' => null,
            ]);
            $presetId = (int) $pdo->lastInsertId();

            $dashboard = $this->dispatchApp('GET', '/audit?source=task', [
                'auth_user' => $admin,
            ]);

            $result = $this->dispatchApp('POST', '/audit/presets/' . $presetId . '/delete', $dashboard['session'], [
                '_token' => (string) ($dashboard['session']['_csrf_token'] ?? ''),
                'return_to' => '/audit?source=task',
            ]);

            $this->assertSame('/audit?source=task', $result['redirect_to']);
            $this->assertSame('Audit-Preset wurde geloescht.', $result['session']['_flash']['success'] ?? null);

            $countStatement = $pdo->prepare(
                'SELECT COUNT(*) FROM audit_filter_presets WHERE id = :id'
            );
            $countStatement->execute(['id' => $presetId]);

            $this->assertSame('0', (string) $countStatement->fetchColumn());
        });
    }

    public function testNonAdminCannotStoreAuditPreset(): void
    {
        $this->withDatabaseTransaction(function (\PDO $pdo): void {
            $user = $this->userByEmail('leiter.it@verwaltung.local');

            $result = $this->dispatchApp('POST', '/audit/presets', [
                'auth_user' => $user,
                '_csrf_token' => 'preset-token',
            ], [
                '_token' => 'preset-token',
                'return_to' => '/audit?source=task',
                'name' => 'Task Review',
                'source' => 'task',
                'search' => '',
                'outcome' => '',
                'date_from' => '',
                'date_to' => '',
            ]);

            $this->assertSame(403, $result['status']);
            $this->assertSame(null, $result['redirect_to']);

            $countStatement = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM audit_filter_presets
                 WHERE user_id = :user_id
                   AND name = :name'
            );
            $countStatement->execute([
                'user_id' => (int) $user['id'],
                'name' => 'Task Review',
            ]);

            $this->assertSame('0', (string) $countStatement->fetchColumn());
        });
    }
}
