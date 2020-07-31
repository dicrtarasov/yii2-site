<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 31.07.20 18:40:33
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\file\FileInputWidget;
use dicr\helper\ArrayHelper;
use dicr\helper\Html;
use dicr\helper\Url;
use dicr\widgets\RedactorWidget;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\bootstrap4\ActiveField;
use yii\bootstrap4\ActiveForm;
use yii\db\ActiveRecord;

/**
 * Форма редактирования.
 */
class EditForm extends ActiveForm
{
    /** @inheritDoc */
    public $layout = 'horizontal';

    /** @inheritDoc */
    public $enableAjaxValidation = true;

    /** @inheritDoc */
    public $fieldConfig = [
        'horizontalCssClasses' => [
            'label' => ['col-sm-3', 'col-xl-2'],
            'offset' => 'offset-sm-3 offset-xl-2',
            'wrapper' => 'col-sm-9 col-xl-10',
            'hint' => '',
            'error' => '',
        ]
    ];

    /**
     * @inheritDoc
     */
    public function init()
    {
        Html::addCssClass($this->options, 'dicr-admin-edit-form');

        if (! isset($this->options['enctype'])) {
            $this->options['enctype'] = 'multipart/form-data';
        }

        parent::init();
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        EditFormAsset::register($this->view);

        $this->view->registerJs("
            $('#{$this->options['id']}').on('afterValidate', function (event, messages, errorAttributes) {
                if (messages) {
                    $.each(messages, function(field, messages) {
                        if (messages && messages[0]) {
                            window.dicr.widgets.toasts.error(messages[0]);
                        }
                    });
                }
            });
        ");

        return parent::run();
    }

    /**
     * Статическое поле
     *
     * @param Model $model
     * @param string $attribute
     * @param array $options для form-group (для самого input использовать inputOptions)
     * @return ActiveField
     */
    public function fieldStatic(Model $model, string $attribute, array $options = [])
    {
        $options['options'] = $options['options'] ?? [];
        Html::addCssClass($options['options'], ['form-group', 'form-group-static', 'row']);

        // баг в bootstrap4 (staticControl не берет inputOptions, сука).
        $inputOptions = ArrayHelper::remove($options, 'inputOptions', []);

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        return $this->field($model, $attribute, $options)->staticControl($inputOptions);
    }

    /**
     * Поле ID
     *
     * @param ActiveRecord $model
     * @param array $options
     * - string|bool $url - добавить URL к ID
     * @return string|ActiveField
     */
    public function fieldId(ActiveRecord $model, array $options = [])
    {
        if ($model->isNewRecord) {
            return '';
        }

        $url = ArrayHelper::remove($options, 'url', false);
        if (! empty($url)) {
            if ($url === true) {
                $url = $model->{'url'};
            }

            $options['inputOptions'] = array_merge([
                'target' => '_blank'
            ], $options['inputOptions'] ?? []);

            $html = Html::a(Html::encode($model->{'id'}), $url, $options['inputOptions']);

            return $this->fieldHtml($model, 'id', $html, $options);
        }

        return $this->fieldStatic($model, 'id', $options);
    }

    /**
     * Поле Created
     *
     * @param ActiveRecord $model
     * @param array $options
     * @return string|ActiveField
     * @throws InvalidConfigException
     */
    public function fieldCreated(ActiveRecord $model, array $options = [])
    {
        if ($model->isNewRecord) {
            return '';
        }

        if (! isset($options['inputOptions']['value'])) {
            $options['inputOptions']['value'] =
                ! empty($model->created) ? Yii::$app->formatter->asDate($model->created, 'php:d.m.Y H:i:s') : null;
        }

        return $this->fieldStatic($model, 'created', $options);
    }

    /**
     * Поле Updated
     *
     * @param ActiveRecord $model
     * @param array $options
     * @return string|ActiveField
     * @throws InvalidConfigException
     */
    public function fieldUpdated(ActiveRecord $model, array $options = [])
    {
        if ($model->isNewRecord) {
            return '';
        }

        if (! isset($options['inputOptions']['value'])) {
            $options['inputOptions']['value'] =
                ! empty($model->updated) ? Yii::$app->formatter->asDate($model->updated, 'php:d.m.Y H:i:s') : null;
        }

        return $this->fieldStatic($model, 'updated', $options);
    }

    /**
     * Поле Disabled
     *
     * @param Model $model
     * @param array $options
     * @return string|ActiveField
     */
    public function fieldDisabled(Model $model, array $options = [])
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->field($model, 'disabled', $options)->checkbox([
            'value' => $model->disabled ?: date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Поле Enabled.
     *
     * @param Model $model
     * @param array $options
     * @return ActiveField
     */
    public function fieldEnabled(Model $model, array $options = [])
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->field($model, 'enabled', $options)->checkbox();
    }

    /**
     * Поле с Html-контентом.
     *
     * @param Model $model
     * @param string $attribute
     * @param string $html
     * @param array $options
     * @return string|ActiveField
     */
    public function fieldHtml(Model $model, string $attribute, string $html, array $options = [])
    {
        if (! isset($options['parts']['{input}'])) {
            $options['parts']['{input}'] = $html;
        }

        $options['options'] = $options['options'] ?? [];
        Html::addCssClass($options['options'], ['form-group', 'form-group-static', 'row']);

        return $this->field($model, $attribute, $options);
    }

    /**
     * Поле URL.
     *
     * @param ActiveRecord $model
     * @param array $options
     * @return string|ActiveField
     */
    public function fieldUrl(ActiveRecord $model, array $options = [])
    {
        if ($model->isNewRecord) {
            return '';
        }

        $options['inputOptions'] = $options['inputOptions'] ?? [];
        Html::addCssClass($options['inputOptions'], 'form-control-plaintext');

        if (! isset($options['inputOptions']['target'])) {
            $options['inputOptions']['target'] = '_blank';
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $url = $model->url;

        $html = Html::a(Html::encode(Url::to($url, true)), $url, $options['inputOptions']);

        return $this->fieldHtml($model, 'url', $html, $options);
    }

    /**
     * Редактор текста.
     *
     * @param Model $model
     * @param string $attribute
     * @param array $options field options
     * @return ActiveField
     * @throws Exception
     */
    public function fieldText(Model $model, string $attribute, array $options = [])
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->field($model, $attribute, $options)->widget(RedactorWidget::class);
    }

    /**
     * Поле ввода картинок.
     *
     * @param Model $model
     * @param string $attribute
     * @param int $limit
     * @param array $options
     * @return ActiveField
     * @throws Exception
     */
    public function fieldImages(Model $model, string $attribute, int $limit = 0, array $options = [])
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->field($model, $attribute, $options)->widget(FileInputWidget::class, [
            'layout' => 'images',
            'limit' => $limit,
            'accept' => 'image/*',
            'removeExt' => true
        ]);
    }

    /**
     * Поле ввода файлов.
     *
     * @param Model $model
     * @param string $attribute
     * @param int $limit
     * @param array $options
     * @return ActiveField
     * @throws Exception
     */
    public function fieldFiles(Model $model, string $attribute, int $limit = 0, array $options = [])
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->field($model, $attribute, $options)->widget(FileInputWidget::class, [
            'layout' => 'files',
            'limit' => $limit,
            'removeExt' => true
        ]);
    }
}
