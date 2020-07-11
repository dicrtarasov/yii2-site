<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 11.07.20 10:26:44
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
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'payDate' => Yii::t('dicr/site', 'Дата оплаты')
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
        return false;
    }

    /**
     * @inheritDoc
     */
    public function toText()
    {
        return array_merge([
            Yii::t('dicr/site', 'Способ оплаты') => static::name()
        ], parent::toText());
    }
}

