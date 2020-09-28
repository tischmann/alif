<?php

namespace Tischmann\Cli;

class BuyEvent
{

    private $clientId = null;
    private $clientPhone = null;
    private $clientMail = null;
    private $sendMail = null;
    private $sendMessage = null;
    private $purchaseId = null;
    private $purchaseDescr = null;
    private $purchaseCost = null;

    public function __construct(array $args)
    {
        array_shift($args);


        if (!$args) {
            echo "Error: Not enough arguments" . PHP_EOL;
            self::showInfo();
        };

        list($found, $value) = self::parseArgVal("/c", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Client ID is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->clientId = $value;
        }

        list($found, $value) = self::parseArgVal("/n", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Client phone number is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->clientPhone = $value;
        }

        list($found, $value) = self::parseArgVal("/e", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Client email address is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->clientMail = $value;
        }

        list($found, $value) = self::parseArgVal("/p", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Purchase ID is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->purchaseId = $value;
        }

        list($found, $value) = self::parseArgVal("/d", $args);

        if ($found !== false) {
            if ($value === false) {
                echo "Error: Purchase description is not defiend" . PHP_EOL;
                self::showInfo();
            }

            $this->purchaseDescr = $value;
        }

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
        /n  User's phone number
        /e  User's mail address
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
}

$event = new BuyEvent($argv);

exit;
