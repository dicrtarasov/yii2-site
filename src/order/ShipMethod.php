<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 03:17:21
 */

declare(strict_types = 1);
namespace dicr\site\order;

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
    public static function classes(): array
    {
        return (array)(Yii::$app->params['order']['ship']['classes'] ?? []);
    }

    /**
     * Метод доставляет товары (не самовывоз).
     *
     * @return bool
     */
    public static function isDelivery(): bool
    {
        return true;
    }

    /**
     * Адрес доставки в читабельной форме.
     *
     * @return string
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getAddress(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function toText(): array
    {
        return array_merge([
            Yii::t('dicr/site', 'Способ доставки') => static::name()
        ], parent::toText());
    }
}
