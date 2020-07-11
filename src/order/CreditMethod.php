<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 11.07.20 15:26:47
 */

declare(strict_types = 1);
namespace dicr\site\order;

use dicr\helper\Html;
use dicr\helper\Inflector;
use Yii;
use yii\base\InvalidArgumentException;
use function array_combine;
use function array_filter;
use function array_merge;

/**
 * Модель кредитования.
 *
 * @property-read int $minTerm минимальный срок банка, мес
 * @property-read int $maxTerm максимальный срок банка, мес (-1 - без ограничений, 0 - способ не применим)
 * @property int $termLimit ограничение срока рассрочки (по бренду товара или другим условиям сайта)
 * @property-read int $gracePeriod льготный период, мес
 * @property-read int $downPayment первый взнос, грн
 * @property-read int $monthlyCharge ежемесячный платеж, грн
 */
abstract class CreditMethod extends PayMethod
{
    /** @var int срок кредитования, мес */
    public $term;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if (! isset($this->term)) {
            $this->term = $this->minTerm;
        }
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'term' => Yii::t('dicr/site', 'Срок рассрочки')
        ]);
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        $minTerm = $this->minTerm;

        $maxTerm = $this->maxTerm;
        if ($maxTerm === - 1) {
            $maxTerm = 127;
        }

        return array_merge(parent::rules(), [
            ['term', 'required'],
            ['term', 'integer', 'min' => $minTerm, 'max' => $maxTerm],
            ['term', 'filter', 'filter' => 'intval'],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function extraFields()
    {
        $extraFields = ['minTerm', 'maxTerm', 'gracePeriod', 'downPayment', 'monthlyCharge'];

        return array_merge(
            parent::extraFields(),
            array_combine($extraFields, $extraFields)
        );
    }

    /** @var int */
    private $_termLimit;

    /**
     * Ограничение лимита брендами товаров.
     *
     * @return int
     */
    public function getTermLimit()
    {
        if (! isset($this->_termLimit)) {
            $this->_termLimit = - 1;
        }

        return $this->_termLimit;
    }

    /**
     * Устанавливает ограничение
     *
     * @param int $limit
     */
    public function setTermLimit(int $limit)
    {
        if ($limit < 0) {
            throw new InvalidArgumentException('limit');
        }

        $this->_termLimit = $limit;
    }

    /**
     * @inheritDoc
     */
    public static function isCredit()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function classes()
    {
        return array_filter(static::classes(), static function(string $class) {
            /** @var PayMethod $class */
            return $class::isCredit();
        });
    }

    /**
     * Название банка.
     *
     * @return string
     */
    public static function bank()
    {
        return '';
    }

    /**
     * Картинка.
     *
     * @return string
     */
    public static function image()
    {
        return static::icon();
    }

    /**
     * Краткие условия кредита.
     *
     * @return string html
     */
    public static function conditions()
    {
        return '';
    }

    /**
     * Описание кредита.
     *
     * @return string html
     */
    public static function desc()
    {
        return '';
    }

    /**
     * Минимальный срок кредита, мес.
     *
     * @return int
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getMinTerm()
    {
        return 0;
    }

    /**
     * Максимальный срок кредита, мес
     *
     * @return int (0 - кредит не приемлем)
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getMaxTerm()
    {
        return - 1;
    }

    /**
     * Шаг месяцев.
     *
     * @return int
     */
    public static function termStep()
    {
        return 1;
    }

    /**
     * Первый взнос.
     *
     * @return float
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getDownPayment()
    {
        return 0;
    }

    /**
     * Льготный период, мес.
     *
     * @return int
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getGracePeriod()
    {
        return 0;
    }

    /**
     * Возвращает сумму ежемесячного платежа, грн
     *
     * @return float
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getMonthlyCharge()
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getIsAvailable()
    {
        // добавляем проверку по сроку
        return parent::getIsAvailable() && ($this->maxTerm > 0);
    }

    /**
     * Сохраняет кредит в сессии.
     */
    public function saveCredit()
    {
        Yii::$app->session->set(__CLASS__, $this->config);
    }

    /**
     * Возвращает сохраненные параметры кредита.
     *
     * @return self|null
     */
    public static function loadCredit()
    {
        $config = Yii::$app->session->get(__CLASS__);
        return ! empty($config) ? static::create($config) : null;
    }

    /**
     * Удаляет сохраненный кредит.
     */
    public static function cleanCredit()
    {
        Yii::$app->session->remove(__CLASS__);
    }

    /**
     * @inheritDoc
     */
    public function toText()
    {
        $data = parent::toText();

        if ($this->term > 0) {
            $data[Yii::t('app', 'Срок рассрочки')] = Html::esc(
                $this->term . ' ' . Inflector::numMonthes($this->term)
            );
        }

        return $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return static::name() . ', ' . $this->term . ' мес.';
    }
}
