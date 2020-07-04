<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.07.20 13:23:38
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use yii\base\Model;
use yii\grid\DataColumn;
use yii\helpers\Html;

/**
 * Колонка-ссылка на редактирование объекта.
 *
 * @noinspection PhpUnused
 */
class EditLinkColumn extends DataColumn
{
    /**
     * @inheritDoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if (($this->content === null) && ($model instanceof Model) && $model->canGetProperty('id')) {
            $value = $this->getDataCellValue($model, $key, $index);

            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            return Html::a($this->grid->formatter->format($value, $this->format),
                ['edit', 'id' => $model->id],
                ['data-pjax' => false]
            );
        }

        return parent::renderDataCellContent($model, $key, $index);
    }
}
