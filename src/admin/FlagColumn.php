<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.07.20 13:39:09
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use yii\grid\DataColumn;
use yii\helpers\Html;

/**
 * Булева колонка таблицы.
 *
 * @noinspection PhpUnused
 */
class FlagColumn extends DataColumn
{
    /** @var array */
    public $headerOptions = [
        'class' => 'text-center'
    ];

    /** @var array */
    public $contentOptions = [
        'class' => 'text-center'
    ];

    /**
     * @inheritDoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content === null) {
            return Html::tag('i', '', [
                'class' => [$this->getDataCellValue($model, $key, $index) ? 'fas' : 'far', 'fa-star']
            ]);
        }

        return parent::renderDataCellContent($model, $key, $index);
    }
}
