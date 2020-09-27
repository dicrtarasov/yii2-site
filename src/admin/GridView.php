<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 28.09.20 02:34:27
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
        'class' => 'table table-sm table-striped'
    ];

    /** @inheritDoc */
    public $layout = '{summary}<div class="table-responsive">{items}</div>{pager}';

    /** @var string аттрибут обозначающий отключенную запись */
    public $disabledAttr = 'disabled';

    /** @var string аттрибут обозначающий включенную запись */
    public $enabledAttr = 'enabled';

    /** @var string атрибут для выделения */
    public $featuredAttr;

    /** @var array */
    private $_origRowOptions;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init() : void
    {
        if (empty($this->pager['class'])) {
            $this->pager['class'] = LinkPager::class;
        }

        $this->_origRowOptions = $this->rowOptions ?: [];

        $this->rowOptions = function ($model, $key, $index, $grid) : array {
            return $this->getRowOptions($model, $key, $index, $grid);
        };

        parent::init();

        Html::addCssClass($this->options, 'dicr-site-admin-grid-view');
    }

    /**
     * Возвращает опции строки.
     *
     * @param Model|array $model
     * @param mixed $key
     * @param int $index
     * @param self $grid
     * @return array
     */
    protected function getRowOptions($model, $key, int $index, self $grid) : array
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
}
