<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 20.07.20 02:43:19
 */

declare(strict_types = 1);
namespace dicr\site\order;

use dicr\helper\Html;
use Yii;
use function array_merge;

/**
 * Метод доставки.
 *
 * @property-read string $address адрес доставки
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
     * Адрес доставки в читабельной форме.
     *
     * @return string
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getAddress()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function toText()
    {
        return array_merge([
            Html::esc(Yii::t('dicr/site', 'Способ доставки')) => static::name()
        ], parent::toText());
    }
}
