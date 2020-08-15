<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 02:07:08
 */

declare(strict_types = 1);
namespace dicr\site\order;

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
    public function getProds(): array;

    /**
     * Сумма товаров.
     *
     * @return float
     */
    public function getSum(): float;

    /**
     * Модель контактов.
     *
     * @return Model
     * Конкретный тип return переопределяется в реализации.
     */
    public function getOrderContacts();

    /**
     * Способ оплаты.
     *
     * @return ?PayMethod
     */
    public function getPayMethod(): ?PayMethod;

    /**
     * Способ доставки.
     *
     * @return ?ShipMethod
     */
    public function getShipMethod(): ?ShipMethod;
}
