<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.01.22 23:25:12
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
    public function attributeLabels(): array
    {
        return [
            'address' => Yii::t('app', 'Адрес')
        ];
    }

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

    /**
     * {@inheritDoc}
     *
     * Сохранить параметры этого метода как выбранного метода оплаты.
     */
    public function saveSelected(): static
    {
        Yii::$app->session->set(__CLASS__, $this->config);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * Восстанавливает сохраненный выбранный метод оплаты.
     *
     * @return ?self
     */
    public static function restoreSelected(bool $clean = false): ?static
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
