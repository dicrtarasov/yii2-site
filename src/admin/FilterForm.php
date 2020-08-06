<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 06.08.20 23:54:06
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;
use Yii;
use yii\base\Model;
use yii\bootstrap4\ActiveField;
use yii\bootstrap4\ActiveForm;

/**
 * Форма фильтра данных.
 */
class FilterForm extends ActiveForm
{
    /** @inheritDoc */
    public $method = 'get';

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (empty($this->action)) {
            $this->action = ['/' . Yii::$app->requestedRoute];
        }

        if (! isset($this->fieldConfig['template'])) {
            $this->fieldConfig['template'] = '{beginWrapper}{input}{hint}{error}{endWrapper}';
        }

        if (! isset($this->options['data-pjax']) && ! isset($this->options['data']['pjax'])) {
            $this->options['data']['pjax'] = 1;
        }

        parent::init();

        Html::addCssClass($this->options, 'dicr-admin-filter-form');
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->view->registerJs("$('#{$this->options['id']}').on('change', ':input', function() {
                $(this).closest('form').submit()
            })");

        return parent::run();
    }

    /**
     * @inheritDoc
     * @return ActiveField
     */
    public function field($model, $attribute, $options = []) : ActiveField
    {
        $attrName = Html::getAttributeName($attribute);
        $prompt = '- ' . mb_strtolower($model->getAttributeLabel($attrName)) . ' -';

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::field($model, $attribute, array_merge([
            'inputOptions' => [
                'placeholder' => $model->getAttributeLabel($attribute),
                'prompt' => $prompt
            ]
        ], $options))->input('search'); // по-умолчанию форматируем в тип search
    }

    /**
     * Булево поле фильтра
     *
     * @param Model $model
     * @param string $attribute
     * @param array $options
     * @return ActiveField
     */
    public function fieldBoolean(Model $model, string $attribute, array $options = []) : ActiveField
    {
        return $this->field($model, $attribute)->dropdownList([
            0 => 'нет',
            1 => 'да'
        ], $options);
    }

    /**
     * Поле Enabled
     *
     * @param Model $model
     * @param array $options
     * @return ActiveField
     */
    public function fieldEnabled(Model $model, array $options = []) : ActiveField
    {
        return $this->fieldBoolean($model, 'enabled', $options);
    }

    /**
     * Поле фильтра по disabled.
     *
     * @param Model $model
     * @param array $options
     * @return ActiveField
     */
    public function fieldDisabled(Model $model, array $options = []) : ActiveField
    {
        return $this->field($model, 'disabled')->dropdownList([
            0 => 'включено',
            1 => 'отключено'
        ], $options);
    }
}
