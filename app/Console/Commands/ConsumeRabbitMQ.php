<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQConsumer;

class ConsumeRabbitMQ extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected $description = 'Consume messages from RabbitMQ queue';

    public function handle()
    {
        $this->info('Starting RabbitMQ consumer...');
        
        $consumer = new RabbitMQConsumer();
        $consumer->consume();
        
        $this->info('RabbitMQ consumer stopped.');
    }
} 