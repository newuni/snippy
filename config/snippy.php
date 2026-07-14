<?php

return [
    'parent_site' => [
        'name' => env('SNIPPY_PARENT_SITE_NAME'),
        'url' => env('SNIPPY_PARENT_SITE_URL'),
    ],

    'agents' => [
        'root_robots_url' => env('SNIPPY_ROOT_ROBOTS_URL'),
        'allow_ai_training' => env('SNIPPY_ALLOW_AI_TRAINING', false),
    ],
];
