<?php

require __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Tokyo');

if (! empty($_POST)) {
    $client = new Maknz\Slack\Client(getenv('SLACK_WEBHOOK_URL'), [
        'username'   => getenv('SLACK_USERNAME'),
        'channel'    => getenv('SLACK_CHANNEL'),
        'icon'       => getenv('SLACK_ICON'),
        'link_names' => true
    ]);

    ORM::configure('sqlite:./d-frag.db');
    $reports = ORM::for_table('reports')->select('reports.*')->find_many();

    $text     = '';
    $datetime = new DateTimeImmutable();
    $current  = $datetime->format('Y-m-d H:i:s');

    foreach ($reports as $report) {
        if (900 < strtotime($current) - strtotime($report['datetime'])) {
            $text .= $report['name'] . 'さんが日報を作成したらしいです。';
            $text .= "\n";
            $text .= $report['content'];
            $text .= "\n";
            $text .= '---------- cut ----------';
            $text .= "\n";

            ORM::for_table('reports')->where_equal('id', $report['id'])->delete_many();
        }
    }

    if ($text !== '') {
        $client->send($text);
    }
}
