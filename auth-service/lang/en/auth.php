<?php

declare(strict_types=1);

return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Registration messages
    'registration_successful' => 'User registered successfully.',
    'registration_failed' => 'Registration failed. Please try again.',
    'user_already_exists' => 'User already exists with this email address.',

    // Login messages
    'login_successful' => 'Login successful.',
    'login_failed' => 'Invalid credentials provided.',
    'invalid_credentials' => 'Invalid credentials provided.',
    'logout_successful' => 'Logged out successfully.',

    // Token messages
    'token_refreshed' => 'Token refreshed successfully.',
    'token_invalid' => 'Invalid or expired token.',
    'token_expired' => 'Token has expired.',

    // Password reset
    'password_reset_sent' => 'Password reset link sent to your email.',
    'password_reset_failed' => 'Unable to send password reset link.',
    'password_reset_successful' => 'Password reset successfully.',
    'password_reset_invalid' => 'Invalid password reset token.',

    // Email verification
    'email_verified' => 'Email verified successfully.',
    'email_already_verified' => 'Email already verified.',
    'email_verification_sent' => 'Verification email sent.',
    'email_verification_failed' => 'Unable to send verification email.',
    'email_verification_invalid' => 'Invalid verification link.',
    'email_not_verified' => 'Email address not verified.',

    // Account status
    'account_disabled' => 'Account has been disabled.',
    'account_not_verified' => 'Please verify your email address first.',
];
