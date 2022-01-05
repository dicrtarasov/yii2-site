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
use yii\base\Model;
use yii\grid\DataColumn;

/**
 * Колонка-ссылка на редактирование объекта.
 */
class EditLinkColumn extends DataColumn
{
    /**
     * @inheritDoc
     */
    protected function renderDataCellContent($model, $key, $index): string
    {
        if ($this->content === null && $model instanceof Model && $model->canGetProperty('id')) {
            $value = $this->getDataCellValue($model, $key, $index);

            return Html::a($this->grid->formatter->format($value, $this->format),
                ['edit', 'id' => $model->{'id'}],
                ['data-pjax' => 0]
            );
        }

        return parent::renderDataCellContent($model, $key, $index);
    }
}
