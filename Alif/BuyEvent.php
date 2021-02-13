<?php

namespace Alif;

use Exception;

class BuyEvent
{
    public Client $client;
    public Product $product;
    public array $notifications = [
        's' => 'sendSms',
        'e' => 'sendEmail'
    ];

    public function __construct(array $args)
    {
        array_shift($args);

        if (!$args) {
            $this->error("Not enough arguments");
            $this->showInfo();
        };

        // Parsing arguments
        $this->parseArgs($args);
    }

    function parseArgs(array $args)
    {
        // Show help info
        list($found, $value) = $this->parseArgVal("-?", $args);

        if ($found !== false) {
            $this->showInfo();
        }

        // Client ID
        list($found, $value) = $this->parseArgVal("-c", $args);

        if ($found === false || $value === false) {
            $this->error("Client ID is not defiend");
            $this->showInfo();
        }

        $this->client = new Client($value);

        if (!$this->client->id) {
            $this->error("Client {$value} not found");
        }

        // Product ID
        list($found, $value) = $this->parseArgVal("-p", $args);

        if ($found === false || $value === false) {
            $this->error("Product ID is not defiend");
            $this->showInfo();
        }

        $this->product = new Product($value);

        if (!$this->product->id) {
            $this->error("Product {$value} not found");
        }

        // Notifications
        list($found, $value) = $this->parseArgVal("-n", $args);

        $message = ucfirst("{$this->product->name} have been purchased successfully");

        if ($found !== false && $value !== false) {
            $keys = str_split($value);
            $keys = array_unique($keys);

            foreach ($keys as $key) {
                $method = $this->notifications[$key] ?? '';

                if (method_exists($this, $method)) {
                    $message .= ". " . $this->{$method}();
                }
            }
        }

        $this->response($message, 1);
    }

    function showInfo()
    {
        echo <<<EOF

Usage:\e[1m buyevent [options]\e[0m

\e[1m-?\e[0m            \e[3m Help\e[0m
\e[1m-c [int]\e[0m      \e[3m Client ID\e[0m
\e[1m-p [int]\e[0m      \e[3m Product ID\e[0m
\e[1m-n [string]\e[0m   \e[3m Notification method (s - SMS, e - E-mail)\e[0m

EOF;
        exit;
    }

    function parseArgVal(string $key, array $args): array
    {
        $found = array_search($key, $args);
        $value = $args[$found + 1] ?? false;
        return [$found, $value];
    }

    function sendSms(): string
    {
        if ($this->client->phone) {
            try {
                // Sending SMS
                return "SMS sent to {$this->client->phone}";
            } catch (Exception $e) {
                $this->error("[{$e->getCode()}]: {$e->getMessage()}");
            }
        } else {
            $this->error("Client has'nt phone number");
        }
    }

    function sendEmail(): string
    {
        if ($this->client->email) {
            try {
                // Sending email
                return "E-mail sent to {$this->client->email}";
            } catch (Exception $e) {
                $this->error("[{$e->getCode()}]: {$e->getMessage()}");
            }
        } else {
            $this->error("Client has'nt email address");
        }
    }

    function response(string $message, int $status = 0)
    {
        $response = [
            'status' => $status,
            'message' => $message
        ];

        die(json_encode($response) . PHP_EOL);
    }

    function error(string $message)
    {
        App::log($message);
        $this->response($message);
    }
}
