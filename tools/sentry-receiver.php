<?php

declare(strict_types=1);

if (! in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
    || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
    || parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) !== '/api/9/envelope/') {
    http_response_code(404);
    exit;
}

$payload = file_get_contents('php://input', false, null, 0, 500_000);
if (($_SERVER['HTTP_CONTENT_ENCODING'] ?? '') === 'gzip') {
    $payload = gzdecode($payload, 500_000);
}

$lines = explode("\n", $payload ?: '', 4);
$item = json_decode($lines[1] ?? '', true);
if (($item['type'] ?? '') === 'event') {
    $event = json_decode($lines[2] ?? '', true);
    $exception = $event['exception']['values'][0] ?? [];
    $request = $event['request'] ?? [];
    $frames = $exception['stacktrace']['frames'] ?? [];
    $stack = array_map(static fn (array $frame): string => ($frame['filename'] ?? '?').':'.($frame['lineno'] ?? '?')
        .' '.($frame['function'] ?? ''), array_slice($frames, -4));
    $email = $event['user']['email'] ?? '';
    $summary = [
        'event_id' => $event['event_id'] ?? null,
        'exception' => $exception['type'] ?? null,
        'message' => $exception['value'] ?? null,
        'request' => ['method' => $request['method'] ?? null, 'path' => parse_url($request['url'] ?? '', PHP_URL_PATH) ?: null],
        'user' => ['id' => $event['user']['id'] ?? null, 'email' => str_ends_with($email, '.test') ? $email : null],
        'stack' => $stack,
    ];
    error_log('Sentry SDK envelope: '.json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

header('Content-Type: application/json');
echo '{"accepted":true}';
