<?php

return [
    'main' => [
        'Sincerely' => 'Sincerely,',
        'Team' => 'Team ":value"',
        'hi' => 'Hello, :value!',
    ],

    'user' => [
        'registered' => [
            'subject' => 'Welcome to :value! 🎉',
            'content' => [
                'main' => 'Welcome to :value! Your account has been successfully created. Now you can manage your time and tasks effectively.',
                'list' => [
                    'Where to start' => 'Where to start?',
                    'Create your first project' => 'Create your first project:',
                    'Define the main areas of work' => 'Define the main areas of work.',
                    'Start tracking your time' => 'Start tracking your time:',
                    'Start a timer for the current task' => 'Start a timer for the current task.',
                    'View reports' => 'View reports:',
                    'Analyze your productivity' => 'Analyze your productivity.',
                ],
                'button' => 'Start Tracking Now',
            ],
        ],
        'updated_email' => [
            'subject' => 'Your email in :value has been changed',
            'content' => [
                'main' => 'This notification confirms that the email associated with your :value account has changed.',
                'list' => [
                    'Prev email' => 'Previous email:',
                    'New email' => 'New email:',
                ],
                'Not requested' => 'If you did not request this change, please contact our support team immediately to ensure the security of your account.',
            ],
        ],
        'updated_password' => [
            'subject' => 'The password for your account :value has been changed',
            'content' => [
                'main' => 'The password for your :app-name (:email) account has been successfully changed.',
                'Not requested' => 'If you did not request this change, please contact our support team immediately to ensure the security of your account.',
            ],
        ],
        'email_verification' => [
            'subject' => 'Your verification code for :value',
            'content' => [
                'main' => 'Your confirmation code:',
                'Not requested' => 'If you did not request this change, please contact our support team immediately to ensure the security of your account.',
            ],
        ],
    ],
];