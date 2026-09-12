<?php

return [
    'fake_enabled' => env('FAKE_PAYMENTS_ENABLED', in_array(env('APP_ENV'), ['local', 'testing'])),
];
