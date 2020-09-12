<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 13.09.20 01:51:53
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
    public function toText() : array
    {
        return array_merge([
            Yii::t('dicr/site', 'Способ доставки') => static::name()
        ], parent::toText());
    }

    /**
     * {@inheritDoc}
     *
     * Сохранить параметры этого метода как выбранного метода оплаты.
     */
    public function saveSelected() : void
    {
        Yii::$app->session->set(__CLASS__, $this->config);
    }

    /**
     * {@inheritDoc}
     *
     * Восстанавливает сохраненный выбранный метод оплаты.
     *
     * @return ?self
     */
    public static function restoreSelected(bool $clean = false) : ?self
    {
        $config = Yii::$app->session->get(__CLASS__);
        if ($config === null) {
            return null;
        }

        $method = static::create($config);

        if ($clean) {
            Yii::$app->session->remove(__CLASS__);
        }

        return $method;
    }
}
