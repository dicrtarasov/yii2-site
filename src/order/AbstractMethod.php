<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 13.09.20 01:47:55
 */

declare(strict_types = 1);
namespace dicr\site\order;

use dicr\helper\Html;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\bootstrap4\ActiveForm;

use function array_combine;
use function array_filter;
use function array_merge;
use function str_replace;
use function strtolower;

/**
 * Базовый класс метода оплаты, доставки или др.
 *
 * @property CheckoutInterface|null $checkout форма оформления заказа
 * @property OrderInterface|null $order оформленный заказ
 * @property float $sum сумма товаров
 * @property-read int $minSum минимальная допустимая сумма товаров
 * @property-read ?int $maxSum максимально приемлемая сумма товаров (null - нет лимита)
 * @property-read bool $isAvailable метод приемлем для заданной суммы и товаров
 * @property float $tax комиссия метода оплаты или доставки
 * @property-read array $config конфиг объекта для сохранения в JSON
 * @property-read string $class класс метода
 */
abstract class AbstractMethod extends Model
{
    /**
     * Текстовое название метода.
     *
     * @return string
     */
    abstract public static function name(): string;

    /**
     * Иконка метода.
     *
     * @return string URL картинки
     */
    public static function icon(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), ['tax']);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'tax' => Yii::t('dicr/site', 'Комиссия')
        ]);
    }

    /**
     * @inheritDoc
     */
    public function extraFields()
    {
        $extraFields = ['minSum', 'maxSum'];

        return array_merge(
            parent::extraFields(),
            array_combine($extraFields, $extraFields)
        );
    }

    /**
     * Конвертирует название класса в id, пригодный для html-аттрибутов.
     *
     * @return string значение для class или id
     */
    public static function id(): string
    {
        return strtolower(str_replace('\\', '-', static::class));
    }

    /**
     * Класс метода.
     *
     * @return string
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getClass(): string
    {
        return static::class;
    }

    /** @var float */
    private $_sum;

    /**
     * Сумма товаров.
     *
     * @return float
     */
    public function getSum(): float
    {
        if (! isset($this->_sum)) {
            if (! empty($this->checkout)) {
                $this->_sum = $this->checkout->getSum();
            } elseif (! empty($this->order)) {
                $this->_sum = $this->order->getSum();
            }
        }

        return $this->_sum ?: 0;
    }

    /**
     * Установить сумму.
     *
     * @param float $sum
     * @return $this
     */
    public function setSum(float $sum): self
    {
        if ($sum < 0) {
            throw new InvalidArgumentException('sum');
        }

        $this->_sum = $sum;

        return $this;
    }

    /**
     * Минимально допустимая сумма товаров для данного метода.
     *
     * @return int
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getMinSum(): int
    {
        return 0;
    }

    /**
     * Максимально допустимая сумма для метода.
     *
     * @return ?int (null - не ограничено)
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getMaxSum(): ?int
    {
        return null;
    }

    /**
     * Метод приемлем для заданных товаров и суммы.
     *
     * @return bool
     */
    public function getIsAvailable(): bool
    {
        $sum = $this->sum;
        $minSum = $this->minSum;
        $maxSum = $this->maxSum;

        return ($sum >= $minSum) && ($maxSum === null || $sum <= $maxSum);
    }

    /** @var float */
    private $_tax = 0;

    /**
     * Комиссия метода доставки или оплаты.
     *
     * @return float
     */
    public function getTax(): float
    {
        return $this->_tax;
    }

    /**
     * Установить комиссию.
     *
     * @param float $tax
     * @return $this
     */
    public function setTax(float $tax): self
    {
        if ($tax < 0) {
            throw new InvalidArgumentException('tax');
        }

        $this->tax = 0;

        return $this;
    }

    /** @var ?CheckoutInterface */
    private $_checkout;

    /**
     * Форма оформления заказа.
     *
     * @return ?CheckoutInterface
     * Не устанавливаем жесткий тип return, потому что переопределяется в реализации.
     */
    public function getCheckout()
    {
        return $this->_checkout;
    }

    /**
     * Установить форму оформления заказа.
     *
     * @param ?CheckoutInterface $checkout
     * @return $this
     */
    public function setCheckout(?CheckoutInterface $checkout): self
    {
        $this->_checkout = $checkout;

        return $this;
    }

    /** @var ?OrderInterface */
    private $_order;

    /**
     * Оформленный заказ.
     *
     * @return ?OrderInterface
     * Не устанавливаем жесткий тип return, потому что переопределяется в реализации.
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Установить заказ.
     *
     * @param ?OrderInterface $order
     */
    public function setOrder(?OrderInterface $order)
    {
        $this->_order = $order;
    }

    /**
     * Возвращает список классов реализации методов.
     *
     * @return string[]
     */
    abstract public static function classes(): array;

    /**
     * Список экземпляров методов.
     *
     * @return static[] экземпляры моделей, индексированные по class.
     */
    public static function list(): array
    {
        $list = [];

        foreach (static::classes() as $class) {
            /** @var self $class */
            $list[(string)$class] = $class::instance();
        }

        return $list;
    }

    /**
     * Список названий, индексированный по class
     *
     * @return string[]
     */
    public static function names(): array
    {
        $list = [];

        foreach (static::classes() as $class) {
            /** @var static $class */
            $list[(string)$class] = $class::name();
        }

        return $list;
    }

    /**
     * Рендерит параметры метода оплаты.
     *
     * @param ActiveForm $form форма
     * @param array $options опции тега
     * @return string HTML
     * @noinspection PhpUnusedParameterInspection
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function render(ActiveForm $form, array $options = []): string
    {
        return '';
    }

    /**
     * Обработка способа оплаты или доставки.
     *
     * @return bool результат обработки
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function process(): bool
    {
        return true;
    }

    /**
     * Создает экземпляр метода из конфига
     *
     * @param array $config
     * @return ?static
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public static function create(array $config): ?self
    {
        if (! empty($config)) {
            try {
                return Yii::createObject($config);
            } catch (InvalidConfigException $ex) {
                Yii::error((string)$ex, __METHOD__);
            }
        }

        return null;
    }

    /**
     * Данные для хранения в JSON.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return array_merge([
            'class' => static::class
        ], array_filter($this->attributes, static function ($val) {
            return $val !== null && $val !== '';
        }));
    }

    /**
     * Текстовые данные для сообщения.
     *
     * @return string[]
     */
    public function toText(): array
    {
        $data = [];

        $attributes = $this->attributes;
        if (empty($this->tax)) {
            $attributes['tax'] = '';
        }

        foreach ($attributes as $name => $val) {
            $val = (string)$val;
            if ($val !== '') {
                $data[Html::esc($this->getAttributeLabel($name))] = Html::esc($val);
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return static::name();
    }

    /**
     * Сохранить параметры метода как выбранного
     */
    abstract public function saveSelected() : void;

    /**
     * Восстановить параметры сохраненного выбранного метода.
     *
     * @param bool $clean удалить сохраненные параметры
     * @return ?static (переопределяется в наследуемом)
     */
    abstract public static function restoreSelected(bool $clean = false);
}
