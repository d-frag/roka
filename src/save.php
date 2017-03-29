<?php

require __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Tokyo');

if (! isset($_POST['access_token'])) {
    http_response_code(400);
    echo json_encode([
        'message' => 'The access token is not specified.',
    ]);
}

if (getenv('ROKA_ACCESS_TOKEN') !== $_POST['access_token']) {
    http_response_code(400);
    echo json_encode([
        'message' => 'The access token is incorrect.',
    ]);
}

ORM::configure('sqlite:./d-frag.db');
$db = ORM::get_db();
$db->exec('
    CREATE TABLE IF NOT EXISTS reports (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        datetime TEXT,
        content TEXT
    );'
);

$reports = ORM::for_table('reports')->create();
$reports->name     = $_POST['name']     ?? 'd-frag';
$reports->datetime = $_POST['datetime'] ?? '0000-00-00 00:00:00';
$reports->content  = $_POST['content']  ?? 'd-frag';
$reports->save();

http_response_code(200);
echo json_encode([
    'message' => 'It was successfully saved.',
]);
