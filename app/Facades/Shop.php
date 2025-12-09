<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getCart()
 * @method static void addToCart(int $productId, int $quantity)
 * @method static void removeFromCart(int $productId)
 * @method static void clearCart()
 * @method static array checkout(array $data)
 * 
 * @see \App\Services\ShopManager
 */
class Shop extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'shop';
    }
}
