<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 11.07.20 09:56:35
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
 * @property-read float $sum сумма товаров
 * @property-read float $minSum минимальная приемлемая сумма товаров
 * @property-read float $maxSum максимально приемлемая для метода сумма товаров (-1 - нет ограничений, 0 - метод не
 *     приемлем)
 * @property-read bool $isAvailable метод приемлем для заданной суммы и товаров
 * @property float $tax комиссия метода оплаты или доставки
 * @property-read array $config конфиг объекта для сохранения в JSON
 */
abstract class AbstractMethod extends Model
{
    /**
     * Текстовое название метода.
     *
     * @return string
     */
    abstract public static function name();

    /**
     * Иконка метода.
     *
     * @return string URL картинки
     */
    public static function icon()
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
     * @return string
     */
    public static function id()
    {
        return strtolower(str_replace('\\', '-', static::class));
    }

    /**
     * Сумма товаров.
     *
     * @return float
     */
    public function getSum()
    {
        if (! empty($this->checkout)) {
            return $this->checkout->getSum();
        }

        if (! empty($this->order)) {
            return $this->order->getSum();
        }

        return 0;
    }

    /**
     * Минимально допустимая сумма товаров для данного метода.
     *
     * @return float
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getMinSum()
    {
        return 0;
    }

    /**
     * Максимально допустимая сумма для метода.
     *
     * @return float (1 - не ограничена, 0 - метод не применим)
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function getMaxSum()
    {
        return - 1;
    }

    /**
     * Метод приемлем для заданных товаров и суммы.
     *
     * @return bool
     */
    public function getIsAvailable()
    {
        $sum = $this->sum;
        $minSum = $this->minSum;
        $maxSum = $this->maxSum;
        return ($sum >= $minSum) && ($maxSum < 0 || $sum <= $maxSum);
    }

    /** @var float */
    private $_tax = 0;

    /**
     * Комиссия метода доставки или оплаты.
     *
     * @return float
     */
    public function getTax()
    {
        return $this->_tax;
    }

    /**
     * Установить комиссию.
     *
     * @param float $tax
     */
    public function setTax(float $tax)
    {
        if ($tax < 0) {
            throw new InvalidArgumentException('tax');
        }

        $this->tax = 0;
    }

    /** @var CheckoutInterface|null */
    private $_checkout;

    /**
     * Форма оформления заказа.
     *
     * @return CheckoutInterface|null
     */
    public function getCheckout()
    {
        return $this->_checkout;
    }

    /**
     * Установить форму оформления заказа.
     *
     * @param CheckoutInterface|null $checkout
     */
    public function setCheckout(CheckoutInterface $checkout = null)
    {
        $this->_checkout = $checkout;
    }

    /** @var OrderInterface|null */
    private $_order;

    /**
     * Оформленный заказ.
     *
     * @return OrderInterface|null
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Установить заказ.
     *
     * @param OrderInterface|null $order
     */
    public function setOrder(OrderInterface $order = null)
    {
        $this->_order = $order;
    }

    /**
     * Возвращает список классов реализации методов.
     *
     * @return string[]
     */
    abstract public static function classes();

    /**
     * Список экземпляров методов.
     *
     * @return static[] экземпляры моделей, индексированные по class.
     */
    public static function list()
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
    public static function names()
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
    public function render(ActiveForm $form, array $options = [])
    {
        return '';
    }

    /**
     * Обработка способа оплаты или доставки.
     *
     * @return bool результат обработки
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function process()
    {
        return true;
    }

    /**
     * Создает экземпляр метода из конфига
     *
     * @param array $config
     * @return static|null
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public static function create(array $config)
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
    public function getConfig()
    {
        return array_merge([
            'class' => static::class
        ], array_filter($this->attributes, static function($val) {
            return $val !== null && $val !== '';
        }));
    }

    /**
     * Текстовые данные для сообщения.
     *
     * @return string[]
     */
    public function toText()
    {
        $data = [];

        foreach ($this->attributes as $name => $val) {
            $data[Html::esc($this->getAttributeLabel($name))] = Html::esc((string)$val);
        }

        return array_filter($data, static function($val) {
            return $val !== null && $val !== '';
        });
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return static::name();
    }
}
