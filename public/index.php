<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Expose-Headers: Content-Disposition');
    header('Access-Control-Max-Age: 86400');
    header('Content-Type: application/json');
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
