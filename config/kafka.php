<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Kafka Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the kafka connections below you wish
    | to use as your default connection for all kafka work.
    |
    */
    'default' => env('KAFKA_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Kafka Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the kafka connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    */
    'connections' => [
        'default' => [
            'consumer' => [
                'brokers' => env('KAFKA_BROKERS', 'localhost:9092'),
                'auto_commit' => env('KAFKA_AUTO_COMMIT', true),
                'group_id' => env('KAFKA_CONSUMER_GROUP_ID', 'laravel_group'),
                'offset_reset' => env('KAFKA_OFFSET_RESET', 'latest'),
                'compression' => env('KAFKA_COMPRESSION_TYPE', 'snappy'),
                'sleep_on_error' => env('KAFKA_SLEEP_ON_ERROR', 5),
                'partition' => env('KAFKA_PARTITION', 0),
                'read_timeout' => env('KAFKA_READ_TIMEOUT', 120),
                'commit_batch_size' => env('KAFKA_COMMIT_BATCH_SIZE', 1),
                'heartbeat_interval_ms' => env('KAFKA_HEARTBEAT_INTERVAL_MS', 3000),
                'session_timeout_ms' => env('KAFKA_SESSION_TIMEOUT_MS', 45000),
                'max_poll_interval_ms' => env('KAFKA_MAX_POLL_INTERVAL_MS', 300000),
                'max_poll_records' => env('KAFKA_MAX_POLL_RECORDS', 1000),
            ],
            'producer' => [
                'brokers' => env('KAFKA_BROKERS', 'localhost:9092'),
                'compression' => env('KAFKA_COMPRESSION_TYPE', 'snappy'),
                'timeout' => env('KAFKA_TIMEOUT_MS', 10000),
                'required_acknowledgment' => env('KAFKA_REQUIRED_ACKNOWLEDGMENT', -1),
                'is_async' => env('KAFKA_IS_ASYNC', false),
                'max_poll_records' => env('KAFKA_MAX_POLL_RECORDS', 1000),
                'flush_attempts' => env('KAFKA_FLUSH_ATTEMPTS', 10),
                'partition' => env('KAFKA_PARTITION', 0),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kafka Topics
    |--------------------------------------------------------------------------
    |
    | Here you can define the Kafka topics used by your application.
    |
    */
    'topics' => [
        'order_notifications' => env('KAFKA_ORDER_NOTIFICATIONS_TOPIC', 'order-notifications'),
    ],
];
