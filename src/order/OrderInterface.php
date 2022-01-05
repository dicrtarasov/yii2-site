<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 22:43:18
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
     */
    public function getSum(): float;
}
