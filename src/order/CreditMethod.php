<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 22:43:03
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
    /** срок кредитования, мес */
    public int $term;

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
     */
    public static function bank() : string
    {
        return '';
    }

    /**
     * Картинка.
     */
    public static function image() : string
    {
        return static::icon();
    }

    /**
     * Краткие условия кредита.
     */
    public static function conditions() : string
    {
        return '';
    }

    /**
     * Описание кредита.
     */
    public static function desc() : string
    {
        return '';
    }

    private ?int $_termLimit = null;

    /**
     * Ограничение срока рассрочки (например брендом товара).
     */
    public function getTermLimit() : ?int
    {
        return $this->_termLimit;
    }

    /**
     * Устанавливает ограничение
     */
    public function setTermLimit(?int $limit): static
    {
        if ($limit !== null && $limit < 0) {
            throw new InvalidArgumentException('limit');
        }

        $this->_termLimit = $limit;

        return $this;
    }

    /**
     * Минимальный срок кредита, мес.
     *
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
     */
    public static function termStep() : int
    {
        return 1;
    }

    /**
     * Первый взнос.
     *
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getDownPayment() : float
    {
        return 0;
    }

    /**
     * Льготный период, мес.
     *
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getGracePeriod() : int
    {
        return 0;
    }

    /**
     * Возвращает сумму ежемесячного платежа, грн
     *
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getMonthlyCharge(): float
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getIsAvailable() : bool
    {
        // проверяем доступность по сумме
        if (! parent::getIsAvailable()) {
            return false;
        }

        // проверяем доступность по сроку
        $minTerm = $this->minTerm;
        $maxTerm = $this->maxTerm;

        // добавляем проверку по сроку
        return $minTerm > 0 && ($maxTerm === null || $maxTerm >= $minTerm);
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

    public function __toString() : string
    {
        return static::name() . ', ' . $this->term . ' ' . Yii::t('dicr/site', 'мес') . '.';
    }
}
