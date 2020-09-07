<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 04:13:01
 */

declare(strict_types = 1);
namespace dicr\site\order;

/**
 * Интерфейс формы оформления заказа.
 */
interface CheckoutInterface
{
    /**
     * Возвращает сумму заказа.
     *
     * @return float
     */
    public function getSum(): float;
}
