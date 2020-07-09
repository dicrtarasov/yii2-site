<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 09.07.20 06:31:26
 */

declare(strict_types = 1);

namespace dicr\site;

use yii\base\Model;

/**
 * Интерфейс заказа.
 */
interface OrderInterface
{
    /**
     * Товары.
     *
     * @return array
     */
    public function getProds();

    /**
     * Сумма товаров.
     *
     * @return float
     */
    public function getSum();

    /**
     * Модель контактов.
     *
     * @return Model
     */
    public function getOrderContacts();

    /**
     * Способ оплаты.
     *
     * @return PayMethod
     */
    public function getPayMethod();

    /**
     * Способ доставки.
     *
     * @return ShipMethod
     */
    public function getShipMethod();
}
