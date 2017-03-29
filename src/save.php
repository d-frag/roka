<?php

require __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Tokyo');

if (! isset($_POST['access_token'])) {
    header('HTTP/1.1 400 Bad Request');
    header('Content-type: application/json');
    echo json_encode([
        'message' => 'The access token is not specified.',
    ]);
}

if (getenv('ROKA_ACCESS_TOKEN') !== $_POST['access_token']) {
    header('HTTP/1.1 400 Bad Request');
    header('Content-type: application/json');
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

header('HTTP/1.1 200 OK');
header('Content-type: application/json');
echo json_encode([
    'message' => 'It was successfully saved.',
]);
