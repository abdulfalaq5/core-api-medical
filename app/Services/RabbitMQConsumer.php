<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class RabbitMQConsumer
{
    protected $connection;
    protected $channel;
    protected $queueName;
    protected $maxRetries = 3;
    protected $retryDelay = 5; // seconds

    public function __construct()
    {
        $this->queueName = config('services.rabbitmq.queue', 'data_sync');
        $this->initializeConnection();
    }

    protected function initializeConnection()
    {
        $retries = 0;
        while ($retries < $this->maxRetries) {
            try {
                $this->connection = new AMQPStreamConnection(
                    config('services.rabbitmq.host', 'localhost'),
                    config('services.rabbitmq.port', 5672),
                    config('services.rabbitmq.username', 'guest'),
                    config('services.rabbitmq.password', 'guest'),
                    config('services.rabbitmq.vhost', '/'),
                    false,
                    'AMQPLAIN',
                    null,
                    'en_US',
                    3.0, // connection timeout
                    3.0  // read/write timeout
                );
                $this->channel = $this->connection->channel();
                return;
            } catch (\Exception $e) {
                $retries++;
                Log::error("RabbitMQ connection attempt {$retries} failed: " . $e->getMessage());
                if ($retries < $this->maxRetries) {
                    sleep($this->retryDelay);
                } else {
                    throw new \Exception("Failed to connect to RabbitMQ after {$this->maxRetries} attempts: " . $e->getMessage());
                }
            }
        }
    }

    public function consume()
    {
        try {
            $this->channel->queue_declare(
                $this->queueName,
                false,
                true,
                false,
                false
            );

            $this->channel->basic_consume(
                $this->queueName,
                '',
                false,
                false,
                false,
                false,
                [$this, 'processMessage']
            );

            while ($this->channel->is_consuming()) {
                try {
                    $this->channel->wait();
                } catch (\PhpAmqpLib\Exception\AMQPConnectionClosedException $e) {
                    Log::error('Connection lost, attempting to reconnect: ' . $e->getMessage());
                    $this->close();
                    $this->initializeConnection();
                    $this->consume();
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error('RabbitMQ Consumer Error: ' . $e->getMessage());
        } finally {
            $this->close();
        }
    }

    public function processMessage(AMQPMessage $message)
    {
        try {
            $data = json_decode($message->body, true);
            
            if (!isset($data['action']) || !isset($data['data'])) {
                throw new \Exception('Invalid message format. Missing action or data.');
            }

            $table = $data['table'];
            $action = $data['action'];
            $antrianData = $data['data'];

            if($table == 'categories'){
                // Process based on action type
                switch ($action) {
                    case 'create':
                    $this->insertOrUpdateCategory($antrianData);
                    break;
                case 'update':
                    $this->insertOrUpdateCategory($antrianData);
                    break;
                case 'delete':
                    $this->deleteCategory($antrianData);
                    break;
                default:
                    throw new \Exception('Unknown action type: ' . $action);
                }
            } elseif($table == 'products'){
                // Process based on action type
                switch ($action) {
                    case 'create':
                        $this->insertOrUpdateProduct($antrianData);
                        break;
                    case 'update':
                        $this->insertOrUpdateProduct($antrianData);
                        break;
                    case 'delete':
                        $this->deleteProduct($antrianData);
                        break;
                }
            } else {
                throw new \Exception('Unknown table: ' . $table);
            }

            // Acknowledge the message
            $message->ack();
            Log::info("Successfully processed {$action} action for category", ['data' => $antrianData]);
        } catch (\Exception $e) {
            Log::error('Error processing message: ' . $e->getMessage(), [
                'message' => $message->body,
                'error' => $e->getMessage()
            ]);
            // Reject the message and requeue it
            $message->reject(true);
        }
    }

    protected function insertOrUpdateCategory(array $data)
    {
        DB::beginTransaction();
        try {
            // Validate required fields
            if (!isset($data['name'])) {
                throw new \Exception('Name is required for category');
            }

            DB::connection('pgsql_query')
                    ->table('categories')
                    ->updateOrInsert(
                        ['id' => $data['id']],
                        $data
                    );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function insertOrUpdateProduct(array $data)
    {
        DB::beginTransaction();
        try {
            // Validate required fields
            if (!isset($data['name'])) {
                throw new \Exception('Name is required for category');
            }

            DB::connection('pgsql_query')
                    ->table('products')
                    ->updateOrInsert(
                        ['id' => $data['id']],
                        $data
                    );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function deleteCategory(array $data)
    {
        DB::beginTransaction();
        try {
            // Validate required fields
            if (!isset($data['id'])) {
                throw new \Exception('ID is required for category deletion');
            }

            // Delete from query database
            DB::connection('pgsql_query')
                ->table('categories')
                ->where('id', $data['id'])
                ->delete();

            DB::commit();
            Log::info('Category deleted successfully', ['id' => $data['id']]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting category: ' . $e->getMessage(), ['id' => $data['id']]);
            throw $e;
        }
    }

    protected function deleteProduct(array $data)
    {
        DB::beginTransaction();
        try {
            // Validate required fields
            if (!isset($data['id'])) {  
                throw new \Exception('ID is required for product deletion');
            }

            // Delete from query database
            DB::connection('pgsql_query')
                ->table('products') 
                ->where('id', $data['id'])
                ->delete();

            DB::commit();
            Log::info('Product deleted successfully', ['id' => $data['id']]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting product: ' . $e->getMessage(), ['id' => $data['id']]);
            throw $e;
        }
    }
    

    public function close()
    {
        try {
            if ($this->channel && $this->channel->is_open()) {
                $this->channel->close();
            }
            if ($this->connection && $this->connection->isConnected()) {
                $this->connection->close();
            }
        } catch (\Exception $e) {
            Log::error('Error closing RabbitMQ connection: ' . $e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->close();
    }
} 