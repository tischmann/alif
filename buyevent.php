#!/usr/bin/php

<?php

namespace Alif\Cli;

use Exception;

class BuyEvent
{

    private $clientId = null; // Client ID
    private $clientPhone = null; // Client phone number
    private $clientMail = null; // Client E-mail address
    private $purchaseId = null; // Purchase ID
    private $purchaseDescr = null; // Purchase description
    private $purchaseCost = null; // Purchase cost
    const LOG_FILE  = "buyevent.log"; // Path to log file

    public function __construct(array $args)
    {
        array_shift($args);

        if (!$args) {
            echo "Error: Not enough arguments" . PHP_EOL;
            self::showInfo();
        };

        // Parsing arguments

        // Show help info
        list($found, $value) = self::parseArgVal("/?", $args);
        if ($found !== false) self::showInfo();

        // Client ID
        list($found, $value) = self::parseArgVal("/c", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Client ID is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->clientId = $value;
        }

        // Client phone number
        list($found, $value) = self::parseArgVal("/n", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Client phone number is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->clientPhone = $value;
        }

        // Client email address
        list($found, $value) = self::parseArgVal("/e", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Client email address is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->clientMail = $value;
        }

        // Purchase ID
        list($found, $value) = self::parseArgVal("/p", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Purchase ID is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->purchaseId = $value;
        }

        // Purchase description
        list($found, $value) = self::parseArgVal("/d", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Purchase description is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->purchaseDescr = $value;
        }

        // Purchase cost
        list($found, $value) = self::parseArgVal("/m", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Purchase is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->purchaseCost = $value;
        }
    }

    public static function showInfo()
    {
        echo <<<EOF

Usage:
    buyevent.php [/? | /c [int] [/n [string] | /e [string]] /p [int] /d [string] /m [string]]

Arguments:
        /?  Help
        /c  User's ID
        /n  User's phone number (if set will send text message)
        /e  User's mail address (if set will send email message)
        /p  Purchase ID
        /d  Purchase description
        /m  Purchase cost

EOF;
        exit;
    }

    private static function parseArgVal(string $key, array $args)
    {
        $found = array_search($key, $args);
        $value = $args[$found + 1] ?? false;
        return [$found, $value];
    }

    public function sendNotification()
    {
        if ($this->clientMail) {
            try {
                $message = "You've just purchased {$this->purchaseDescr} (ID:{$this->purchaseId}) for {$this->purchaseCost}$";
                mail($this->clientMail, "Purchase", $message);
            } catch (Exception $ex) {
                self::error($ex->getMessage());
            }
        }

        if ($this->clientPhone) {
            // Send text message
        }

        echo "OK" . PHP_EOL;
    }

    private static function error(string $text)
    {
        $errorText = "Error: {$text}";
        self::log($errorText);
        echo $errorText;
        exit;
    }

    private static function log(string $text)
    {
        $logText = "[" . date("Y-m-d H:i:s") . "] {$text}";

        try {
            file_put_contents(self::LOG_FILE, $logText, FILE_APPEND | LOCK_EX);
        } catch (Exception $ex) {
            echo "Error: " . $ex->getMessage();
            exit;
        }
    }
}

$event = new BuyEvent($argv);

$event->sendNotification();

exit;
