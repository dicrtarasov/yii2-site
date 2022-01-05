<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 22:51:56
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\helper\Html;
use dicr\validate\PhoneValidator;
use NumberFormatter;
use Yii;

use function date;
use function idate;
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
    public $currencyDecimalSeparator = '.';

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

    /** код страны для PhoneFormatter */
    public ?int $phoneCountry = 7;

    /** код региона для PhoneFormatter */
    public ?int $phoneRegion;

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();

        $this->defaultTimeZone = Yii::$app->timeZone;
    }

    /**
     * Форматирует как текстовую строку (удаляя html-теги и экранируя) с ограничением длины.
     */
    public function asString(mixed $value, int $limit = 0): string
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
    public function asCurrency($value, $currency = null, $options = [], $textOptions = []): string
    {
        if ($value === null || $value === '') {
            return $this->nullDisplay;
        }

        if (! isset($options[NumberFormatter::MAX_FRACTION_DIGITS])) {
            $options[NumberFormatter::MAX_FRACTION_DIGITS] = (float)$value - (int)$value === 0 ? 0 : 2;
        }

        return parent::asCurrency($value, $currency, $options, $textOptions);
    }

    /**
     * Форматирует телефон.
     */
    public function asPhone(mixed $value, array $options = []): string
    {
        $phoneValidator = new PhoneValidator([
                'country' => $this->phoneCountry,
                'region' => $this->phoneRegion
            ] + $options);

        return $phoneValidator->formatValueSilent($value);
    }

    /**
     * Форматирует телефон.
     */
    public static function phone(mixed $value, array $options = []): string
    {
        /** @var static $self */
        $self = Yii::$app->formatter;

        return $self->asPhone($value, $options);
    }

    /**
     * Форматирует как длинную дату "25 января 2004"
     */
    public static function asFullDate(string $date): string
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
     */
    public static function fullDate(string $date): string
    {
        return static::asFullDate($date);
    }
}
