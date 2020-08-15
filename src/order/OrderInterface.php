<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 04:12:19
 */

declare(strict_types = 1);
namespace dicr\site\order;

/**
 * Интерфейс заказа.
 */
interface OrderInterface
{
    /**
     * Сумма товаров.
     *
     * @return float
     */
    public function getSum(): float;
}
