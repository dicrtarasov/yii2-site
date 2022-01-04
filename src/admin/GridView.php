<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 00:04:38
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;
use yii\base\Arrayable;
use yii\base\InvalidConfigException;
use yii\base\Model;

use function array_key_exists;
use function is_callable;

/**
 * GridView.
 */
class GridView extends \yii\grid\GridView
{
    /** @inheritDoc */
    public $tableOptions = [
        'class' => 'table table-sm table-striped table-hover'
    ];

    /** @inheritDoc */
    public $layout = '{summary}<div class="table-responsive">{items}</div>{pager}';

    /** @var ?string аттрибут обозначающий отключенную запись */
    public ?string $disabledAttr = 'disabled';

    /** @var ?string аттрибут обозначающий включенную запись */
    public ?string $enabledAttr = 'enabled';

    /** @var ?string атрибут для выделения */
    public ?string $featuredAttr = null;

    private array $_origRowOptions;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (empty($this->pager['class'])) {
            $this->pager['class'] = LinkPager::class;
        }

        $this->_origRowOptions = $this->rowOptions ?: [];

        $this->rowOptions = fn($model, $key, $index, $grid): array => $this->getRowOptions(
            $model, $key, $index, $grid
        );

        parent::init();

        Html::addCssClass($this->options, 'dicr-site-admin-grid-view');
    }

    /**
     * Возвращает опции строки.
     */
    protected function getRowOptions(Model|array $model, mixed $key, int $index, self $grid): array
    {
        // оригинальные опции
        $options = $this->_origRowOptions;

        // если опции в виде Closure, то получаем значение
        if (is_callable($options)) {
            $options = $options($model, $key, $index, $grid);
        }

        if (! empty($model)) {
            if ($model instanceof Model) {
                $model = $model->attributes;
            } elseif ($model instanceof Arrayable) {
                $model = $model->toArray();
            } else {
                $model = (array)$model;
            }

            $disabled = false;
            $featured = false;

            if (! empty($this->disabledAttr) && array_key_exists($this->disabledAttr, $model)) {
                $disabled = ! empty($model[$this->disabledAttr]);
            } elseif (! empty($this->enabledAttr) && array_key_exists($this->enabledAttr, $model)) {
                $disabled = empty($model[$this->enabledAttr]);
            }

            if (! empty($this->featuredAttr) && array_key_exists($this->featuredAttr, $model)) {
                $featured = ! empty($model[$this->featuredAttr]);
            }

            if ($disabled) {
                Html::addCssStyle($options, ['text-decoration' => 'line-through']);
            }

            if ($featured) {
                Html::addCssStyle($options, ['font-weight' => 'bold']);
            }
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function run(): string
    {
        AdminAsset::register($this->view);

        return (string)parent::run();
    }
}
