<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 09.07.20 07:54:17
 */

declare(strict_types = 1);
namespace dicr\site\order;

/**
 * Метод доставки.
 */
abstract class ShipMethod extends AbstractMethod
{
    /**
     * Метод доставляет товары (не самовывоз).
     *
     * @return bool
     */
    public static function isDelivery()
    {
        return true;
    }
}
