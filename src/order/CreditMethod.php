<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 28.09.20 02:32:54
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
 * @property-read ?int $maxTerm максимальный срок банка, мес (null - без ограничений, 0 - метод не приемлем)
 * @property ?int $termLimit ограничение срока рассрочки (по бренду товара или другим условиям сайта)
 * @property-read int $gracePeriod льготный период, мес
 * @property-read float $downPayment первый взнос, руб
 * @property-read float $monthlyCharge ежемесячный платеж, руб
 */
abstract class CreditMethod extends PayMethod
{
    /** @var int срок кредитования, мес */
    public $term;

    /**
     * @inheritDoc
     */
    public function init() : void
    {
        parent::init();

        if (! isset($this->term)) {
            $this->term = $this->minTerm;
        }
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels() : array
    {
        return array_merge(parent::attributeLabels(), [
            'term' => Yii::t('dicr/site', 'Срок рассрочки, мес')
        ]);
    }

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return array_merge(parent::rules(), [
            ['term', 'required'],
            ['term', 'integer', 'min' => $this->minTerm, 'max' => $this->maxTerm ?? 127],
            ['term', 'filter', 'filter' => 'intval'],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function extraFields() : array
    {
        $extraFields = ['minTerm', 'maxTerm', 'gracePeriod', 'downPayment', 'monthlyCharge'];

        return array_merge(
            parent::extraFields(),
            array_combine($extraFields, $extraFields)
        );
    }

    /**
     * @inheritDoc
     */
    public static function isCredit() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function classes() : array
    {
        return array_filter(parent::classes(), static function (string $class) : bool {
            /** @var PayMethod $class */
            return $class::isCredit();
        });
    }

    /**
     * Название банка.
     *
     * @return string
     */
    public static function bank() : string
    {
        return '';
    }

    /**
     * Картинка.
     *
     * @return string
     */
    public static function image() : string
    {
        return static::icon();
    }

    /**
     * Краткие условия кредита.
     *
     * @return string html
     */
    public static function conditions() : string
    {
        return '';
    }

    /**
     * Описание кредита.
     *
     * @return string html
     */
    public static function desc() : string
    {
        return '';
    }

    /** @var ?int */
    private $_termLimit;

    /**
     * Ограничение срока рассрочки (например брендом товара).
     *
     * @return ?int
     */
    public function getTermLimit() : ?int
    {
        return $this->_termLimit;
    }

    /**
     * Устанавливает ограничение
     *
     * @param ?int $limit
     */
    public function setTermLimit(?int $limit) : void
    {
        if ($limit !== null && $limit < 0) {
            throw new InvalidArgumentException('limit');
        }

        $this->_termLimit = $limit;
    }

    /**
     * Минимальный срок кредита, мес.
     *
     * @return int
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getMinTerm() : int
    {
        return 1;
    }

    /**
     * Максимальный срок кредита, мес
     *
     * @return ?int (null - не задан)
     */
    public function getMaxTerm() : ?int
    {
        return $this->termLimit;
    }

    /**
     * Шаг месяцев.
     *
     * @return int
     */
    public static function termStep() : int
    {
        return 1;
    }

    /**
     * Первый взнос.
     *
     * @return float
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getDownPayment() : float
    {
        return 0;
    }

    /**
     * Льготный период, мес.
     *
     * @return int
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getGracePeriod() : int
    {
        return 0;
    }

    /**
     * Возвращает сумму ежемесячного платежа, грн
     *
     * @return float
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getMonthlyCharge() : ?float
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getIsAvailable() : bool
    {
        $maxTerm = $this->maxTerm;

        // добавляем проверку по сроку
        return parent::getIsAvailable() && ($maxTerm === null || $maxTerm > 0);
    }

    /**
     * @inheritDoc
     */
    public function toText() : array
    {
        $text = parent::toText();

        if ($this->term > 0) {
            $text[$this->getAttributeLabel('term')] = Html::esc(
                $this->term . ' ' . Inflector::numMonthes($this->term)
            );
        }

        return $text;
    }

    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return static::name() . ', ' . $this->term . ' ' . Yii::t('dicr/site', 'мес') . '.';
    }
}
