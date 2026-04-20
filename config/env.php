<?php
/**
 * Environment loader and helpers.
 *
 * Loads KEY=VALUE pairs from project-root .env only when the key is not
 * already provided by the process environment.
 */

if (!function_exists('app_env_load')) {
    function app_env_load() {
        static $loaded = false;

        if ($loaded) {
            return;
        }

        $loaded = true;
        $envPath = dirname(__DIR__) . '/.env';
        if (!is_readable($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            if (strpos($line, 'export ') === 0) {
                $line = trim(substr($line, 7));
            }

            $separatorPos = strpos($line, '=');
            if ($separatorPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $separatorPos));
            $value = trim(substr($line, $separatorPos + 1));

            if ($key === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                continue;
            }

            $valueLength = strlen($value);
            if ($valueLength >= 2) {
                $first = $value[0];
                $last = $value[$valueLength - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

if (!function_exists('app_env_get')) {
    function app_env_get($key, $default = null) {
        app_env_load();
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

if (!function_exists('app_env_get_int')) {
    function app_env_get_int($key, $default) {
        $value = app_env_get($key, null);
        if ($value === null || $value === '') {
            return (int) $default;
        }

        return is_numeric($value) ? (int) $value : (int) $default;
    }
}

if (!function_exists('app_env_get_bool')) {
    function app_env_get_bool($key, $default) {
        $value = app_env_get($key, null);
        if ($value === null || $value === '') {
            return (bool) $default;
        }

        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $bool === null ? (bool) $default : $bool;
    }
}
