<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */
    'login' => [
        'title' => 'Log in to your account',
        'description' => 'Enter your username and password below to log in',
        'username' => 'Username',
        'password' => 'Password',
        'remember' => 'Remember me',
        'forgot_password' => 'Forgot password?',
        'button' => 'Log in',
        'captcha' => 'Please complete the reCAPTCHA',
        'registration_application' => 'Registration application',
    ],
    'forgot_password' => [
        'title' => 'Forgot password',
        'description' => 'Enter your email to receive a password reset link',
        'email_label' => 'Email address',
        'submit_button' => 'Email password reset link',
        'return_to_login' => 'Or, return to',
        'login_link' => 'log in',
    ],
    'reset_password' => [
        'title' => 'Reset password',
        'description' => 'Please enter your new password below',
        'email_label' => 'Email',
        'password_label' => 'Password',
        'password_placeholder' => 'Password',
        'confirm_label' => 'Confirm password',
        'confirm_placeholder' => 'Confirm password',
        'submit_button' => 'Reset password',
    ],
    'verify_email' => [
        'title' => 'Verify email',
        'description' => 'Please verify your email address by clicking on the link we just emailed to you.',
        'verification_sent' => 'A new verification link has been sent to the email address you provided during registration.',
        'resend_button' => 'Resend verification email',
        'logout_link' => 'Log out',
    ],
    'confirm_password' => [
        'title' => 'Confirm your password',
        'description' => 'This is a secure area of the application. Please confirm your password before continuing.',
        'password_label' => 'Password',
        'password_placeholder' => 'Password',
        'submit_button' => 'Confirm password',
    ],

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

];
