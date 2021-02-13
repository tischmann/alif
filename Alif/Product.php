<?php

namespace Alif;

class Product
{
    public int $id = 0;
    public string $name = '';
    public float $price = 0;

    function __construct(int $id)
    {
        $query = new Query();
        $query->table('products')->where('id', '=', $id)->limit(1);

        foreach ($query->get() as $product) {
            $this->id = $product->id;
            $this->name = $product->name;
            $this->price = round($product->price, 2);
        }
    }
}
