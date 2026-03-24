<?php

declare(strict_types=1);

use App\Services\AuthService;

final class AuthServiceTest extends TestCase
{
    public function testAcceptsStrongPassword(): void
    {
        $service = new AuthService(testApp());

        $service->assertPasswordStrength('StrongPass!2026', 'user@verwaltung.local', 'User Example');
        $this->assertTrue(true);
    }

    public function testRejectsShortPassword(): void
    {
        $service = new AuthService(testApp());

        $this->expectException(static function () use ($service): void {
            $service->assertPasswordStrength('Short1!', 'user@verwaltung.local', 'User Example');
        });
    }

    public function testRejectsPasswordWithoutSpecialCharacter(): void
    {
        $service = new AuthService(testApp());

        $this->expectException(static function () use ($service): void {
            $service->assertPasswordStrength('StrongPass2026', 'user@verwaltung.local', 'User Example');
        });
    }

    public function testRejectsPasswordContainingPersonalIdentifier(): void
    {
        $service = new AuthService(testApp());

        $this->expectException(static function () use ($service): void {
            $service->assertPasswordStrength('UserExample!2026', 'user@verwaltung.local', 'User Example');
        });
    }
}
