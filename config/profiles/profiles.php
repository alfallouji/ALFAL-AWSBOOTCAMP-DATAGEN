<?php

return array(
    // List of config profiles available 
    'configProfiles' => array(
        'sample-local-csv-file' => array(
            // Short comment displayed at the top
            'comment' => 'This template will simulate a game being run numerous times',

            // Template folder name - must be similar to the folder name in profiles/
            'templateFolder' => 'game-base',

            // Total number of entries generated
            'total' => 100,

            // Size of the batch to send to Kinesis
            'batchSize' => 400,

            // Interval for loop in ms
            'interval' => 10000,

            // Implementation to use 
            'implementation' => 'csv',

            // File output
            'file' => '/tmp/dataset.csv',
        ),
        'sample-local-json-file' => array(
            // Short comment displayed at the top
            'comment' => 'This template will simulate a game being run numerous times',

            // Template folder name - must be similar to the folder name in profiles/
            'templateFolder' => 'game-base',

            // Total number of entries generated
            'total' => 100,

            // Size of the batch to send to Kinesis
            'batchSize' => 400,

            // Interval for loop in ms
            'interval' => 10000,

            // Implementation to use 
            'implementation' => 'file',

            // File output
            'file' => '/tmp/dataset.json',
        ),
        'sample-aws-kinesis' => array(
            // Short comment displayed at the top
            'comment' => 'This template will generate some player profile data',

            // Template folder name - must be similar to the folder name in profiles/
            'templateFolder' => 'sample',

            // Name of an existing Kiensis stream
            'streamName' => 'workshopAnalyticsStream',

            // Total number of entries generated
            'total' => 1000,

            // Size of the batch to send to Kinesis
            'batchSize' => 500,

            // Interval for loop in ms
            'interval' => 20000,

            // Region
            'region' => 'us-east-1',

            // Implementation to use 
            'implementation' => 'kinesis',
        ),
        'sample-aws-firehose' => array(
            // Short comment displayed at the top
            'comment' => 'This template will generate some player profile data',

            // Template folder name - must be similar to the folder name in profiles/
            'templateFolder' => 'sample',

            // Name of an existing Kiensis stream
            'streamName' => 'workshopAnalyticsStream',

            // Total number of entries generated
            'total' => 1000,

            // Size of the batch to send to Kinesis
            'batchSize' => 500,

            // Interval for loop in ms
            'interval' => 20000,

            // Region
            'region' => 'us-east-1',

            // Implementation to use 
            'implementation' => 'firehose',
         ),
         'sample-aws-dynamodb' => array(
            // Short comment displayed at the top
            'comment' => 'This template will simulate a game being run numerous times',

            // Template folder name - must be similar to the folder name in profiles/
            'templateFolder' => 'game-base',

            // Total number of entries generated
            'total' => 100,

            // Size of the batch to send to Kinesis
            'batchSize' => 25,

            // Interval for loop in ms
            'interval' => 10000,

            // Region
            'region' => 'us-east-1',

            // Implementation to use 
            'implementation' => 'dynamodb',

            // Table name
            'tableName' => 'datagen-entries',
        ),
        'sample-aws-cloudwatch' => array(
            // Short comment displayed at the top
            'comment' => 'This template will simulate a game being run numerous times',

            // Template folder name - must be similar to the folder name in profiles/
            'templateFolder' => 'game-base',

            // Total number of entries generated
            'total' => 100,

            // Size of the batch to send to Kinesis
            'batchSize' => 25,

            // Interval for loop in ms
            'interval' => 10000,

            // Region
            'region' => 'us-east-1',

            // Implementation to use 
            'implementation' => 'cloudwatchlogs',

            // Log group name
            'groupName' => 'log-datagen',

            // Log stream name
            'streamName' => 'stream-datagen',
        ),
        'sample-aws-sqs' => array(
            // Short comment displayed at the top
            'comment' => 'This template will simulate a game being run numerous times',

            // Template folder name - must be similar to the folder name in profiles/
            'templateFolder' => 'game-base',

            // Total number of entries generated
            'total' => 20,

            // Size of the batch to send to Kinesis
            'batchSize' => 10,

            // Interval for loop in ms
            'interval' => 10000,

            // Region
            'region' => 'us-east-1',

            // Implementation to use 
            'implementation' => 'sqs',

            // SQS
            'queueUrl' => 'https://sqs.us-east-1.amazonaws.com/985419638254/sqs-datagen',
        ),
        'sample-aws-s3' => array(
            // Short comment displayed at the top
            'comment' => 'This template will simulate a game being run numerous times',

            // Template folder name - must be similar to the folder name in profiles/
            'templateFolder' => 'game-base',

            // Total number of entries generated
            'total' => 20,

            // Size of the batch to send to Kinesis
            'batchSize' => 10,

            // Interval for loop in ms
            'interval' => 10000,

            // Region
            'region' => 'us-east-1',

            // Implementation to use 
            'implementation' => 's3',

            // Bucketname
            'bucketName' => 'kinesis-datagen',

            // S3 file prefix
            'prefix' => 'dgen/',
        ),
        'sample-aws-lambda' => array(
            // Short comment displayed at the top
            'comment' => 'This template will simulate a game being run numerous times',

            // Template folder name - must be similar to the folder name in profiles/
            'templateFolder' => 'game-base',

            // Total number of entries generated
            'total' => 20,

            // Size of the batch to send to Kinesis
            'batchSize' => 10,

            // Interval for loop in ms
            'interval' => 10000,

            // Region
            'region' => 'us-east-1',

            // Implementation to use 
            'implementation' => 'lambda',

            // FunctionName
            'functionName' => 'datagen-lambda-fn',
        ),
    ),
);
