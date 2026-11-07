<?php
// Mock of the Google Cloud Translation API v2 for GoogleTranslatorTest (PHP built-in server)
header('Content-Type: application/json; charset=utf-8');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (($_SERVER['HTTP_X_GOOG_API_KEY'] ?? '') !== 'test-key') {
    http_response_code(400);
    echo json_encode(['error' => ['code' => 400, 'message' => 'API key not valid. Please pass a valid API key.']]);
    return;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || strpos($_SERVER['REQUEST_URI'], 'key=') !== false) {
    http_response_code(405);
    echo json_encode(['error' => ['message' => 'POST without the key in the URL expected']]);
    return;
}
if (substr($path, -10) === '/languages') {
    echo json_encode(['data' => ['languages' => [['language' => 'en'], ['language' => 'ru']]]]);
} elseif (substr($path, -7) === '/detect') {
    echo json_encode(['data' => ['detections' => [[['language' => 'en', 'confidence' => 1]]]]]);
} elseif ($_POST['q'] === 'garbage') {
    echo 'not json';
} else {
    echo json_encode(['data' => ['translations' => [['translatedText' => "[{$_POST['target']}/{$_POST['format']}] {$_POST['q']}"]]]]);
}
