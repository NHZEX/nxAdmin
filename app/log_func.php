<?php

function log_emergency($message, array $context = []): void
{
    __log_record_ex('emergency', $message, $context);
}

function log_alert($message, array $context = []): void
{
    __log_record_ex('alert', $message, $context);
}

function log_critical($message, array $context = []): void
{
    __log_record_ex('critical', $message, $context);
}

function log_error($message, array $context = []): void
{
    __log_record_ex('error', $message, $context);
}

function log_warning($message, array $context = []): void
{
    __log_record_ex('warning', $message, $context);
}

function log_notice($message, array $context = []): void
{
    __log_record_ex('notice', $message, $context);
}

function log_info($message, array $context = []): void
{
    __log_record_ex('info', $message, $context);
}

function log_debug($message, array $context = []): void
{
    __log_record_ex('debug', $message, $context);
}

function log_sql($message, array $context = []): void
{
    __log_record_ex('sql', $message, $context);
}

function __log_record_ex(string $level, $message, array $context): void
{
    if (is_array($message) || is_object($message)) {
        $message = var_export($message, true);
    }
    app()->log->log($level, $message, $context);
}
