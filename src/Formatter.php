<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 21.09.20 19:50:11
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\validate\PhoneValidator;
use NumberFormatter;
use Yii;

use function number_format;

/**
 * Форматер
 */
class Formatter extends \yii\i18n\Formatter
{
    /** @inheritDoc */
    public $dateFormat = 'php:d.m.Y';

    /** @inheritDoc */
    public $datetimeFormat = 'php:d.m.y H:i:s';

    /** @inheritDoc */
    public $decimalSeparator = '.';

    /** @inheritDoc */
    public $thousandSeparator = ' ';

    /** @inheritDoc */
    public $numberFormatterSymbols = [
        NumberFormatter::CURRENCY_SYMBOL => '₽'
    ];

    /** @inheritDoc */
    public $currencyCode = 'RUB';

    /** @inheritDoc */
    public $numberFormatterOptions = [
        NumberFormatter::GROUPING_USED => true,
        NumberFormatter::MIN_FRACTION_DIGITS => 0,
        NumberFormatter::MAX_FRACTION_DIGITS => 3
    ];

    /** @var ?int код страны для PhoneFormatter */
    public $phoneCountry;

    /** @var ?int код региона для PhoneFormatter */
    public $phoneRegion;

    /**
     * @inheritDoc
     */
    public function init() : void
    {
        parent::init();

        $this->defaultTimeZone = Yii::$app->timeZone;
    }

    /**
     * @inheritDoc
     *
     * Fix currency decimal separator and symbol formatting bug on INTL_ICU_VERSION 52.1 (up to 56.1)
     */
    public function asCurrency($value, $currency = null, $options = [], $textOptions = []) : string
    {
        if (! empty($currency) || ! empty($options) || ! empty($textOptions)) {
            return parent::asCurrency($value, $currency, $options, $textOptions);
        }

        $precision = (float)$value - (int)$value > 0 ? 2 : 0;
        $value = round($value, $precision);

        return $value === null || $value === '' ? $this->nullDisplay :
            number_format((float)$value, 0, '.', ' ') . ' ' .
            ($this->numberFormatterOptions[NumberFormatter::CURRENCY_SYMBOL] ?? '');
    }

    /**
     * Форматирует телефон.
     *
     * @param mixed $value
     * @param array $options
     * @return string
     */
    public function asPhone($value, array $options = []) : string
    {
        $phoneValidator = new PhoneValidator([
                'phoneCountry' => $this->phoneCountry,
                'phoneRegion' => $this->phoneRegion
            ] + $options);

        return $phoneValidator->formatValueSilent($value);
    }
}
