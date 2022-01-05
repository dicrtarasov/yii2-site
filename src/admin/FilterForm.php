<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 22:54:44
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;
use Yii;
use yii\base\Model;
use yii\bootstrap5\ActiveField;
use yii\bootstrap5\ActiveForm;

use function mb_strtolower;

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
    public function init(): void
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

        Html::addCssClass($this->options, 'dicr-site-admin-filter-form');
    }

    /**
     * @inheritDoc
     */
    public function run(): string
    {
        $this->view->registerJs(
            "$('#{$this->options['id']}').on('change', ':input', function() {
                $(this).closest('form').submit()
            })"
        );

        return parent::run();
    }

    /**
     * @inheritDoc
     */
    public function field($model, $attribute, $options = []): ActiveField
    {
        $attrName = Html::getAttributeName($attribute);

        // добавляем prompt для select
        if (! isset($options['inputOptions']['prompt'])) {
            $options['inputOptions']['prompt'] = '- ' . mb_strtolower($model->getAttributeLabel($attrName)) . ' -';
        }

        // добавляем placeholder
        if (! isset($options['inputOptions']['placeholder'])) {
            $options['inputOptions']['placeholder'] = $model->getAttributeLabel($attrName);
        }

        // по-умолчанию форматируем в тип search
        return parent::field($model, $attribute, $options)
            ->input('search');
    }

    /**
     * Булево поле фильтра
     */
    public function fieldBoolean(Model $model, string $attribute, array $options = []): ActiveField
    {
        return $this->field($model, $attribute, $options)->dropDownList([
            0 => 'нет',
            1 => 'да'
        ]);
    }

    /**
     * Поле Enabled
     */
    public function fieldEnabled(Model $model, array $options = []): ActiveField
    {
        return $this->fieldBoolean($model, 'enabled', $options);
    }

    /**
     * Поле фильтра по disabled.
     */
    public function fieldDisabled(Model $model, array $options = []): ActiveField
    {
        return $this->field($model, 'disabled', $options)->dropDownList([
            0 => 'включено',
            1 => 'отключено'
        ]);
    }
}
