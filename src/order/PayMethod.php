<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 30.10.20 21:38:42
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
    /** @var string дата оплаты заказа */
    public $payDate;

    /**
     * @inheritDoc
     */
    public function attributeLabels() : array
    {
        return array_merge(parent::attributeLabels(), [
            'payDate' => Yii::t('dicr/site', 'Дата оплаты')
        ]);
    }

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return array_merge(parent::rules(), [
            ['payDate', 'default'],
            ['payDate', 'date', 'format' => 'php:Y-m-d H:i:s']
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function classes() : array
    {
        return (array)(Yii::$app->params['order']['pay']['classes'] ?? []);
    }

    /**
     * Способ оплаты в кредит
     *
     * @return bool
     */
    public static function isCredit() : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function toText() : array
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

