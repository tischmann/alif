<?php

namespace Alif;

class Client
{
    public int $id = 0;
    public string $phone = '';
    public string $email = '';

    function __construct(int $id)
    {
        $query = new Query();
        $query->table('clients')->where('id', '=', $id)->limit(1);

        foreach ($query->get() as $client) {
            $this->id = $client->id;
            $this->phone = $client->phone;
            $this->email = $client->email;
        }
    }
}
