<?php

function log_emergency($message, array $context = [])
{
    app()->log->log('emergency', $message, $context);
}

function log_alert($message, array $context = [])
{
    app()->log->log('alert', $message, $context);
}

function log_critical($message, array $context = [])
{
    app()->log->log('critical', $message, $context);
}

function log_error($message, array $context = [])
{
    app()->log->log('error', $message, $context);
}

function log_warning($message, array $context = [])
{
    app()->log->log('warning', $message, $context);
}

function log_notice($message, array $context = [])
{
    app()->log->log('notice', $message, $context);
}

function log_info($message, array $context = [])
{
    app()->log->log('info', $message, $context);
}

function log_debug($message, array $context = [])
{
    app()->log->log('debug', $message, $context);
}

function log_sql($message, array $context = [])
{
    app()->log->log('sql', $message, $context);
}
