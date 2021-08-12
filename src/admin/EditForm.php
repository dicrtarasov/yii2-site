<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.08.21 22:25:44
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
use yii\base\UnknownPropertyException;
use yii\bootstrap5\ActiveField;
use yii\bootstrap5\ActiveForm;
use yii\db\ActiveRecord;

use function date;

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
    public function init(): void
    {
        parent::init();

        if (! isset($this->options['enctype'])) {
            $this->options['enctype'] = 'multipart/form-data';
        }

        Html::addCssClass($this->options, 'dicr-site-admin-edit-form');
    }

    /**
     * @inheritDoc
     */
    public function run(): string
    {
        AdminAsset::register($this->view);

        $this->view->registerJs("
            $('#{$this->options['id']}').on('afterValidateAttribute', function (event, attribute, messages) {
                if (messages && messages.length > 0) {
                    window.dicr.widgets.toasts.error(messages[0]);
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
    public function fieldStatic(Model $model, string $attribute, array $options = []): ActiveField
    {
        $options['options'] ??= [];
        Html::addCssClass($options['options'], ['form-group', 'form-group-static', 'row']);

        // баг в bootstrap4 (staticControl не берет inputOptions, сука).
        $inputOptions = ArrayHelper::remove($options, 'inputOptions', []);

        return $this->field($model, $attribute, $options)
            ->staticControl($inputOptions);
    }

    /**
     * Поле ID.
     *
     * @param ActiveRecord $model
     * @param array $options
     * - string|bool $url - добавить URL к ID
     * @return ?ActiveField
     */
    public function fieldId(ActiveRecord $model, array $options = []): ?ActiveField
    {
        if ($model->isNewRecord) {
            return null;
        }

        if (! isset($options['inputOptions']['value'])) {
            $url = ArrayHelper::remove($options, 'url', true);
            if ($url === true) {
                if ($model->canGetProperty('href')) {
                    $url = $model->{'href'};
                } elseif ($model->canGetProperty('url')) {
                    $url = $model->{'url'};
                } else {
                    $url = null;
                }
            }

            if (! empty($url)) {
                $url = Url::to($url, true);

                if (! isset($options['inputOptions']['target'])) {
                    $options['inputOptions']['target'] = '_blank';
                }

                if (! isset($options['inputOptions']['title'])) {
                    $options['inputOptions']['title'] = 'Страница: ' . $url;
                }

                $options['inputOptions']['value'] = Html::a(
                    Html::encode($model->{'id'}), $url, $options['inputOptions']
                );

                return $this->fieldHtml($model, 'id', $options);
            }
        }

        return $this->fieldStatic($model, 'id', $options);
    }

    /**
     * Поле Created
     *
     * @param ActiveRecord $model
     * @param array $options
     * @return ?ActiveField
     * @throws UnknownPropertyException
     * @throws InvalidConfigException
     */
    public function fieldCreated(ActiveRecord $model, array $options = []): ?ActiveField
    {
        if ($model->isNewRecord) {
            return null;
        }

        if (! isset($options['inputOptions']['value'])) {
            if (! $model->canGetProperty('created')) {
                throw new UnknownPropertyException('created');
            }

            $options['inputOptions']['value'] = ! empty($model->{'created'}) ?
                Yii::$app->formatter->asDate($model->{'created'}, 'php:d.m.Y H:i:s') : null;
        }

        return $this->fieldStatic($model, 'created', $options);
    }

    /**
     * Поле Updated
     *
     * @param ActiveRecord $model
     * @param array $options
     * @return ?ActiveField
     * @throws InvalidConfigException
     */
    public function fieldUpdated(ActiveRecord $model, array $options = []): ?ActiveField
    {
        if ($model->isNewRecord) {
            return null;
        }

        if (! isset($options['inputOptions']['value'])) {
            $options['inputOptions']['value'] = empty($model->{'updated'}) ? null :
                Yii::$app->formatter->asDate($model->{'updated'}, 'php:d.m.Y H:i:s');
        }

        return $this->fieldStatic($model, 'updated', $options);
    }

    /**
     * Поле Enabled.
     *
     * @param Model $model
     * @param array $options
     * @return ActiveField
     */
    public function fieldEnabled(Model $model, array $options = []): ActiveField
    {
        return $this->field($model, 'enabled', $options)->checkbox();
    }

    /**
     * Поле Disabled
     *
     * @param Model $model
     * @param array $options
     * @return ActiveField
     */
    public function fieldDisabled(Model $model, array $options = []): ActiveField
    {
        return $this->field($model, 'disabled', $options)->checkbox([
            'value' => $model->{'disabled'} ?: date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Поле для редактирования datetime-local
     *
     * @param Model $model
     * @param string $attribute
     * @param array $options
     * @return ActiveField
     * @throws InvalidConfigException
     */
    public function fieldDateTime(Model $model, string $attribute, array $options = []): ActiveField
    {
        $attr = Html::getAttributeName($attribute);

        if (! isset($options['inputOptions']['value'])) {
            $options['inputOptions']['value'] = empty($model->{$attr}) ? '' :
                Yii::$app->formatter->asDatetime($model->{$attr}, 'php:Y-m-d\TH:i:s');
        }

        return $this->field($model, $attribute, $options)->input('datetime-local');
    }

    /**
     * Редактирование даты.
     *
     * @param Model $model
     * @param string $attribute
     * @param array $options
     * @return ActiveField
     * @throws InvalidConfigException
     */
    public function fieldDate(Model $model, string $attribute, array $options = []): ActiveField
    {
        $attr = Html::getAttributeName($attribute);

        if (! isset($options['inputOptions']['value'])) {
            $options['inputOptions']['value'] = empty($model->{$attr}) ? '' :
                Yii::$app->formatter->asDate($model->{$attr}, 'php:Y-m-d');
        }

        return $this->field($model, $attribute, $options)->input('date');
    }

    /**
     * Поле с Html-контентом.
     *
     * @param Model $model
     * @param string $attribute
     * @param array $options
     * @return ActiveField
     */
    public function fieldHtml(Model $model, string $attribute, array $options = []): ActiveField
    {
        $attr = Html::getAttributeName($attribute);

        $options['options'] ??= [];
        Html::addCssClass($options['options'], ['form-group', 'form-group-static', 'row']);

        // формируем элемент ввода из html
        if (! isset($options['parts']['{input}'])) {
            // если html не задан в value, то берем значение аттрибута модели
            if (! isset($options['inputOptions']['value'])) {
                $options['inputOptions']['value'] = $model->{$attr};
            }

            $options['parts']['{input}'] = $options['inputOptions']['value'];
        }

        return $this->field($model, $attribute, $options);
    }

    /**
     * Поле URL.
     *
     * @param ActiveRecord $model
     * @param array $options
     * @return ?ActiveField
     */
    public function fieldUrl(ActiveRecord $model, array $options = []): ?ActiveField
    {
        if ($model->isNewRecord) {
            return null;
        }

        $options['inputOptions'] ??= [];
        Html::addCssClass($options['inputOptions'], 'form-control-plaintext');

        if (! isset($options['inputOptions']['target'])) {
            $options['inputOptions']['target'] = '_blank';
        }

        if (! isset($options['inputOptions']['value'])) {
            $url = $model->{'url'};

            $options['inputOptions']['value'] = Html::a(
                Html::encode(Url::to($url, true)), $url, $options['inputOptions']
            );
        }

        return $this->fieldHtml($model, 'url', $options);
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
    public function fieldText(Model $model, string $attribute, array $options = []): ActiveField
    {
        return $this->field($model, $attribute, $options)
            ->widget(RedactorWidget::class);
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
    public function fieldImages(Model $model, string $attribute, int $limit = 0, array $options = []): ActiveField
    {
        return $this->field($model, $attribute, $options)
            ->widget(FileInputWidget::class, [
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
    public function fieldFiles(Model $model, string $attribute, int $limit = 0, array $options = []): ActiveField
    {
        return $this->field($model, $attribute, $options)
            ->widget(FileInputWidget::class, [
                'layout' => 'files',
                'limit' => $limit,
                'removeExt' => true
            ]);
    }
}
