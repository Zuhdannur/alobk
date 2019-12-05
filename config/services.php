<?php

return [

    'firebase' => [
        'database_url' => env('FB_DATABASE', 'https://project-id.firebaseio.com/'),
        'secret' => env('FB_DATABASE_KEY', 'dbsecretkey'),
    ],

];
