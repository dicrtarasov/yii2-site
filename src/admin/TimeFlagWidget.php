<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 09.07.20 14:03:14
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\widgets\InputWidget;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use function date;

/**
 * Виджет поля ввода datetime с типом checkbox.
 */
class TimeFlagWidget extends InputWidget
{
    /** @var string datetime формат */
    public $format;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function run()
    {
        $this->options['labelOptions'] = [
            'class' => 'control-label',
            'style' => 'font-weight: normal'
        ];

        if ($this->hasModel()) {
            $val = Html::getAttributeValue($this->model, $this->attribute);
            $this->options['value'] = $val ?: date('Y-m-d H:i:s');
            $this->options['label'] = $val ? Yii::$app->formatter->asDatetime($val, $this->format) : '';
            return Html::activeCheckbox($this->model, $this->attribute, $this->options);
        }

        $this->options['value'] = $this->value ?: date('Y-m-d H:i:s');
        $this->options['label'] = $this->value ? Yii::$app->formatter->asDatetime($this->value, $this->format) : '';

        return Html::checkbox($this->name, $this->value, $this->options);
    }
}
