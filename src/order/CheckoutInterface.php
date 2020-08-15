<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 02:05:58
 */

declare(strict_types = 1);
namespace dicr\site\order;

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
    public function getProds(): array;

    /**
     * Возвращает сумму заказа.
     *
     * @return float
     */
    public function getSum(): float;

    /**
     * Форма контактов покупателя
     *
     * @return Model
     * Конкретный тип return переопределяется в реализации
     */
    public function getOrderContacts();

    /**
     * Выбранный метод оплаты.
     *
     * @return ?PayMethod
     */
    public function getPayMethod(): ?PayMethod;

    /**
     * Выбранный метод доставки.
     *
     * @return ?ShipMethod
     */
    public function getShipMethod(): ?ShipMethod;
}
