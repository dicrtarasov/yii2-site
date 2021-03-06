<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 28.09.20 02:33:55
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;
use yii\grid\DataColumn;

/**
 * Булева колонка таблицы.
 */
class FlagColumn extends DataColumn
{
    /** @inheritDoc */
    public $headerOptions = [
        'class' => 'text-center'
    ];

    /** @inheritDoc */
    public $contentOptions = [
        'class' => 'text-center'
    ];

    /**
     * @inheritDoc
     */
    protected function renderDataCellContent($model, $key, $index) : string
    {
        if ($this->content === null) {
            return Html::tag('i', '', [
                'class' => [$this->getDataCellValue($model, $key, $index) ? 'fas' : 'far', 'fa-star']
            ]);
        }

        return parent::renderDataCellContent($model, $key, $index);
    }
}
