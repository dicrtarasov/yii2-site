<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 22:43:40
 */

declare(strict_types = 1);
namespace dicr\site\order;

use Yii;

use function array_merge;

/**
 * Метод оплаты.
 */
abstract class PayMethod extends AbstractMethod
{
    /** дата оплаты заказа */
    public ?string $payDate = null;

    /**
     * @inheritDoc
     */
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'payDate' => Yii::t('dicr/site', 'Дата оплаты')
        ]);
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            ['payDate', 'default'],
            ['payDate', 'date', 'format' => 'php:Y-m-d H:i:s']
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function classes(): array
    {
        return (array)(Yii::$app->params['order']['pay']['classes'] ?? []);
    }

    /**
     * Способ оплаты в кредит
     */
    public static function isCredit(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function toText(): array
    {
        return array_merge(parent::toText(), [
            Yii::t('dicr/site', 'Способ оплаты') => static::name()
        ]);
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
