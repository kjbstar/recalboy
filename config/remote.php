<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Remote Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default connection that will be used for SSH
    | operations. This name should correspond to a connection name below
    | in the server list. Each connection will be manually accessible.
    |
    */

    'default' => 'recalbox',

    /*
    |--------------------------------------------------------------------------
    | Remote Server Connections
    |--------------------------------------------------------------------------
    |
    | These are the servers that will be accessible via the SSH task runner
    | facilities of Laravel. This feature radically simplifies executing
    | tasks on your servers, such as deploying out these applications.
    |
    */

    'connections' => [
        'recalbox' => [
            'host'      => getenv('RECALBOX_IP'),
            'username'  => getenv('RECALBOX_LOGIN'),
            'password'  => getenv('RECALBOX_PASS'),
            'key'       => getenv('RECALBOX_KEY'),
            'keytext'   => getenv('RECALBOX_KEYTEXT'),
            'keyphrase' => getenv('RECALBOX_KEYPHRASE'),
            'agent'     => getenv('RECALBOX_AGENT'),
            'timeout'   => getenv('RECALBOX_TIMEOUT'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Remote Server Groups
    |--------------------------------------------------------------------------
    |
    | Here you may list connections under a single group name, which allows
    | you to easily access all of the servers at once using a short name
    | that is extremely easy to remember, such as "web" or "database".
    |
    */

    'groups' => [
        'web' => ['recalbox'],
    ],

];
