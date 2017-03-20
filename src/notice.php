<?php

require __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Tokyo');

if (! empty($_POST)) {
    ORM::configure('sqlite:./d-frag.db');
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
}
