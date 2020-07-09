<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 09.07.20 06:31:25
 */

declare(strict_types = 1);
namespace dicr\site;

use yii\base\Model;

/**
 * Интерфейс формы оформления заказа.
 */
interface CheckoutInterface
{
    /**
     * Возвращает товары корзины.
     *
     * @return array товары
     */
    public function getProds();

    /**
     * Возвращает сумму заказа.
     *
     * @return mixed
     */
    public function getSum();

    /**
     * Форма контактов покупателя
     *
     * @return Model
     */
    public function getOrderMethod();

    /**
     * Метод оплаты.
     *
     * @return PayMethod
     */
    public function getPayMethod();

    /**
     * Метод доставки.
     *
     * @return ShipMethod
     */
    public function getShipMethod();
}
