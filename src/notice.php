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

$reports = ORM::for_table('reports')->select('reports.*')->find_many();

$datetime = new DateTimeImmutable();
$current  = $datetime->format('Y-m-d H:i:s');

$data = [];
foreach ($reports as $report) {
    if (900 < strtotime($current) - strtotime($report['datetime'])) {
        array_push($data, [
            'name'    => $report['name'],
            'content' => $report['content'],
        ]);

        ORM::for_table('reports')->where_equal('id', $report['id'])->delete_many();
    }
}

if (! empty($data)) {
    $client = new Maknz\Slack\Client(getenv('SLACK_WEBHOOK_URL'), [
        'username'   => getenv('SLACK_USERNAME'),
        'channel'    => getenv('SLACK_CHANNEL'),
        'icon'       => getenv('SLACK_ICON'),
        'link_names' => true
    ]);

    foreach ($data as $datum) {
        $client = $client->attach([
            'color'       => 'good',
            'fallback'    => $datum['content'],
            'text'        => $datum['content'],
            'author_name' => $datum['name'],
            'author_link' => getenv('SLACK_ICON'),
            'author_icon' => getenv('SLACK_ICON'),
        ]);
    }

    $text = '';
    if ($slackText = getenv('SLACK_TEXT')) {
        $text .= $slackText;
    }

    $client->send($text);
}

http_response_code(200);
echo json_encode([
    'message' => 'It was notified successfully.',
]);
