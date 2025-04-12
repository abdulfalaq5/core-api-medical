<?php

namespace App\Helpers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQProducer
{
    public static function publish(string $queue, array $data)
    {
        $connection = new AMQPStreamConnection(
            config('services.rabbitmq.host'),
            config('services.rabbitmq.port'),
            config('services.rabbitmq.user'),
            config('services.rabbitmq.password')
        );

        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);

        $msg = new AMQPMessage(json_encode($data));
        $channel->basic_publish($msg, '', $queue);

        $channel->close();
        $connection->close();
    }
}
