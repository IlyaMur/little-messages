<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Config;

class Config
{
    const DB_HOST = getenv('DB_HOST');
    const DB_NAME = getenv('DB_NAME');
    const DB_USER = getenv('DB_USER');
    const DB_PASSWORD = getenv('DB_PASSWORD');

    const ROOT_URL = '/';
    const APP_VERSION = '1.0.0';

    const SHOW_ERRORS = true;
    const SECRET_KEY = 'dummykey';
}
