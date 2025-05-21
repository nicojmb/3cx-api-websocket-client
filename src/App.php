<?php

namespace Mike3CXIntegration;

use Exception;
use Ratchet\Client\Connector;
use React\EventLoop\Loop;
use React\Socket\Connector as ReactConnector;

class App
{
    private array $config;

    public function __construct()
    {
        $this->getConfig();
    }

    public function run()
    {

        $url = $this->config['base_url'] ?? null;

        $url = str_replace('https://', 'wss://', $url) . '/callcontrol/ws';

        $loop = Loop::get();

        if ($this->config['test_mode'] === true)
        {
            $reactConnector = new ReactConnector($loop, [
                'tls' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
        }
        else
        {
            $reactConnector = new ReactConnector($loop);
        }

        try
        {
            $token = $this->getAccessToken();

            $headers = [
                'Authorization' => "Bearer {$token}",
                'User-Agent' => '3cx-api-websocket-client/1.0',
            ];

            $connector = new Connector($loop, $reactConnector);

            $this->connectToWebSocket($connector, $headers, $url, $loop);
        }
        catch (Exception $e)
        {
            echo "Error: {$e->getMessage()}\n";
        }

        $loop->run();
    }

    private function connectToWebSocket($connector, $headers, $url, $loop)
    {
        $connector($url,  [], $headers)->then(function ($conn) use ($connector, $headers, $url, $loop)
        {
            $loop->addPeriodicTimer($this->config['keep_alive'], function () use ($conn)
            {
                $this->sendMessage((json_encode([
                    'RequestID' => 'keepalive-' . time(),
                    'Path' => '/callcontrol'
                ])), $conn);
            });

            $conn->on('message', function ($msg) use ($conn)
            {
                $this->getMessage($msg, $conn);
            });
        }, function ($e)
        {
            throw new Exception("Error al conectar: {$e->getMessage()}");
        });
    }

    private function sendMessage($msg, $conn)
    {
        $conn->send($msg);
    }

    private function getMessage($msg, $conn)
    {
        $data = json_decode($msg, true);

        if (!isset($data['event']))
        {
            return;
        }

        $event = $data['event'];
        $type = $event['event_type'];
        $entity = $event['entity'];
        $attachedData = $event['attached_data'] ?? null;

        if (str_contains($entity, 'keepalive'))
        {
            return;
        }

        // Si no hay datos adjuntos, hacemos una petición GET
        if (is_null($attachedData) && in_array($type, [0, 1]))
        {
            $requestId = 'get-call-data-' . $type . '-' . basename($entity);
            $conn->send(json_encode([
                'RequestID' => $requestId,
                'Path' => $entity
            ]));
        }

        // Si es una respuesta a un GET
        if ($type === 4 && !empty($attachedData))
        {
            $response = $attachedData['Response'] ?? null;

            if ($response)
            {
                echo "📋 ($entity) STATUS: {$response['status']} - FROM: {$response['dn']} - TO: {$response['party_caller_id']}\n";
            }
            else
            {
                echo "📋 ($entity) FIN\n";
            }
        }
    }


    private function getAccessToken(): string
    {
        $ch = curl_init();

        $data = http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
        ]);

        curl_setopt($ch, CURLOPT_URL, $this->config['base_url'] . '/connect/token');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        if ($this->config['test_mode'] === true)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200)
        {
            $tokenData = json_decode($response, true);
            return $tokenData['access_token'];
        }


        throw new Exception("Error al obtener el token (CODE: $http_code) - (RESPONSE: $response)");
    }

    private function getConfig(): void
    {
        $this->config = require __DIR__ . '/../config/3cx.php';

        if (empty($this->config))
        {
            throw new \Exception('No se ha encontrado la configuración de 3CX');
        }
    }
}
