<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 11.07.20 13:25:58
 */

declare(strict_types = 1);
namespace dicr\site\order;

use Yii;
use function array_merge;

/**
 * Метод доставки.
 */
abstract class ShipMethod extends AbstractMethod
{
    /**
     * @inheritDoc
     */
    public static function classes()
    {
        return (array)(Yii::$app->params['order']['ship']['classes'] ?? []);
    }

    /**
     * Метод доставляет товары (не самовывоз).
     *
     * @return bool
     */
    public static function isDelivery()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function toText()
    {
        return array_merge([
            Yii::t('dicr/site', 'Способ доставки') => static::name()
        ], parent::toText());
    }
}
