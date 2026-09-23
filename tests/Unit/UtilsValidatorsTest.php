<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UtilsValidatorsTest extends TestCase
{
    public function test_email_validation()
    {
        // Valid emails
        $this->assertTrue($this->isValidEmail('test@example.com'));
        $this->assertTrue($this->isValidEmail('user.name@domain.pl'));
        $this->assertTrue($this->isValidEmail('test+tag@gmail.com'));

        // Invalid emails
        $this->assertFalse($this->isValidEmail('invalid'));
        $this->assertFalse($this->isValidEmail('@example.com'));
        $this->assertFalse($this->isValidEmail('test@'));
        $this->assertFalse($this->isValidEmail(''));
    }

    public function test_polish_phone_validation()
    {
        // Valid phones
        $this->assertTrue($this->isValidPhoneNumber('+48500100200'));
        $this->assertTrue($this->isValidPhoneNumber('500100200'));
        $this->assertTrue($this->isValidPhoneNumber('+48 500 100 200'));

        // Invalid phones
        $this->assertFalse($this->isValidPhoneNumber('123'));
        $this->assertFalse($this->isValidPhoneNumber('+49500100200')); // German
        $this->assertFalse($this->isValidPhoneNumber(''));
    }

    public function test_username_validation()
    {
        // Valid usernames
        $this->assertTrue($this->isValidUsername('jankowalski'));
        $this->assertTrue($this->isValidUsername('user_123'));
        $this->assertTrue($this->isValidUsername('test-user'));

        // Invalid usernames
        $this->assertFalse($this->isValidUsername('ab')); // too short
        $this->assertFalse($this->isValidUsername('user@name')); // special chars
        $this->assertFalse($this->isValidUsername('user name')); // space
        $this->assertFalse($this->isValidUsername('')); // empty
    }

    public function test_password_strength()
    {
        // Strong password
        $strong = $this->checkPasswordStrength('MyP@ssw0rd123!');
        $this->assertGreaterThanOrEqual(4, $strong['score']);

        $weak = $this->checkPasswordStrength('pass');
        $this->assertLessThan(2, $weak['score']);

        $medium = $this->checkPasswordStrength('Pass1');
        $this->assertEquals(3, $medium['score']);
    }

    public function test_postal_code_validation()
    {
        // Valid Polish postal codes
        $this->assertTrue($this->isValidPostalCode('00-001'));
        $this->assertTrue($this->isValidPostalCode('99-999'));

        // Invalid
        $this->assertFalse($this->isValidPostalCode('001'));
        $this->assertFalse($this->isValidPostalCode('00001'));
        $this->assertFalse($this->isValidPostalCode(''));
    }

    // Helper methods (simplified versions of frontend validators)

    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isValidPhoneNumber(string $phone): bool
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        return preg_match('/^(\+48)?[0-9]{9}$/', $cleaned) === 1;
    }

    private function isValidUsername(string $username): bool
    {
        return strlen($username) >= 3 && preg_match('/^[a-zA-Z0-9_-]+$/', $username) === 1;
    }

    private function checkPasswordStrength(string $password): array
    {
        $score = 0;
        if (strlen($password) >= 8) {
            $score++;
        }
        if (preg_match('/[a-z]/', $password)) {
            $score++;
        }
        if (preg_match('/[A-Z]/', $password)) {
            $score++;
        }
        if (preg_match('/[0-9]/', $password)) {
            $score++;
        }
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score++;
        }

        $strength = match (true) {
            $score >= 4 => 'strong',
            $score >= 3 => 'medium',
            default => 'weak'
        };

        return ['score' => $score, 'strength' => $strength];
    }

    private function isValidPostalCode(string $code): bool
    {
        return preg_match('/^[0-9]{2}-[0-9]{3}$/', $code) === 1;
    }
}
