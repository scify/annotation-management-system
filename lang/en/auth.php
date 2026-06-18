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
        'button' => 'Log in',
        'captcha' => 'Please complete the reCAPTCHA',
        'registration_application' => 'Registration application',
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
