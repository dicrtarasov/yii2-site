<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 09.07.20 06:23:31
 */

/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 06.07.20 12:02:35
 */

declare(strict_types = 1);
namespace dicr\site;

use Yii;
use function array_merge;
use function is_a;

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
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'payDate' => 'Дата оплаты'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['payDate', 'default'],
            ['payDate', 'date', 'format' => 'php:Y-m-d H:i:s']
        ]);
    }

    /**
     * Способ оплаты в кредит
     */
    public static function isCredit()
    {
        return is_a(static::class, CreditMethod::class, true);
    }

    /**
     * @inheritDoc
     */
    public function toText()
    {
        return array_merge([
            Yii::t('app', 'Способ оплаты') => static::name()
        ], parent::toText());
    }
}

