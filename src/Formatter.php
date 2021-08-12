<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.08.21 20:41:00
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\helper\Html;
use dicr\validate\PhoneValidator;
use NumberFormatter;
use Yii;

use function date;
use function idate;
use function number_format;
use function sprintf;
use function strtotime;
use function substr;

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
    public $numberFormatterOptions = [
        NumberFormatter::GROUPING_USED => 1,
        NumberFormatter::MIN_FRACTION_DIGITS => 0,
        NumberFormatter::MAX_FRACTION_DIGITS => 3
    ];

    /** @inheritDoc */
    public $numberFormatterSymbols = [
        NumberFormatter::CURRENCY_SYMBOL => '₽'
    ];

    /** @inheritDoc */
    public $currencyCode = 'RUB';

    /** @var ?int код страны для PhoneFormatter */
    public $phoneCountry = 7;

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
     * Форматирует как текстовую строку (удаляя html-теги и экранируя) с ограничением длины.
     *
     * @param mixed $value
     * @param int $limit
     * @return string
     */
    public function asString($value, int $limit = 0): string
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        $value = Html::toText((string)$value);

        if ($limit > 0 && mb_strlen($value) > $limit) {
            $value = substr($value, 0, $limit) . '...';
        }

        return Html::encode($value);
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

        $precision = (float)$value - (int)$value !== 0 ? 2 : 0;
        $value = round((float)$value, $precision);

        return ($value === null || $value === '') ? $this->nullDisplay :
            number_format($value, $precision, '.', ' ') . ' ' .
            ($this->numberFormatterSymbols[NumberFormatter::CURRENCY_SYMBOL] ?? '');
    }

    /**
     * Форматирует телефон.
     *
     * @param int|string|null $value
     * @param array $options
     * @return string
     */
    public function asPhone($value, array $options = []): string
    {
        $phoneValidator = new PhoneValidator([
                'country' => $this->phoneCountry,
                'region' => $this->phoneRegion
            ] + $options);

        return $phoneValidator->formatValueSilent($value);
    }

    /**
     * Форматирует телефон.
     *
     * @param string|int|null $value
     * @param array $options
     * @return string
     */
    public static function phone($value, array $options = []): string
    {
        /** @var self $self */
        $self = Yii::$app->formatter;

        return $self->asPhone($value, $options);
    }

    /**
     * Форматирует как длинную дату "25 января 2004"
     *
     * @param string $date
     * @return string
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function asFullDate(string $date): string
    {
        static $monthes = [
            'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа',
            'сентября', 'октября', 'ноября', 'декабря'
        ];

        $time = strtotime($date);
        if (empty($time)) {
            return '';
        }

        return sprintf('%d %s %d',
            date('d', $time),
            $monthes[idate('m', $time) - 1],
            date('Y', $time)
        );
    }

    /**
     * Форматирует как длинную дату "25 января 2004"
     *
     * @param string $date
     * @return string
     */
    public static function fullDate(string $date) : string
    {
        /** @var self $formatter */
        $formatter = Yii::$app->formatter;

        return $formatter->asFullDate($date);
    }
}
