<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\CartService cart()
 * @method static \App\Services\CheckoutService checkoutService()
 * @method static \App\Services\GreenterService greenter()
 * @method static \App\Services\InvoiceService invoice()
 * @method static \App\Services\InventoryService inventory()
 * @method static \App\Services\PaymentService payment()
 * @method static \App\Services\ShippingService shipping()
 * @method static array getCart()
 * @method static void addToCart(int $productId, int $quantity)
 * @method static void removeFromCart(int $productId)
 * @method static void clearCart()
 * @method static mixed processCheckout(array $data)
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
