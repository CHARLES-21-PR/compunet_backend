<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'PENDIENTE';
    case PAID = 'PAGADO';
    case DELIVERED = 'ENTREGADO';
    case CANCELLED = 'CANCELADO';
    case FAILED = 'FALLIDO';
}
