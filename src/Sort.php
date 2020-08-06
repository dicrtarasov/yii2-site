<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 07.08.20 00:08:42
 */

declare(strict_types = 1);

namespace dicr\site;

use Yii;
use function count;
use function is_array;
use function strncmp;
use function substr;
use const SORT_ASC;
use const SORT_DESC;

/**
 * Class Sort
 *
 * @property-read string $sort текущее значение параметра сортировки
 * @property-read bool $isDefault является ли сортировка по-умолчанию
 */
class Sort extends \yii\data\Sort
{
    /**
     * Возвращает текущее значение параметра сортировки.
     *
     * @param bool $emptyIfDefault возвратить пустую строку если сортировка по-умолчанию
     * @return string текущую сортировку 'attr' или '-attr'
     */
    public function getSort(bool $emptyIfDefault = false) : string
    {
        $params = $this->params ?? Yii::$app->request->queryParams;
        $sort = $params[$this->sortParam] ?? '';

        // если сортировка не задана или не нужно сравнивать с умолчанием, то возвращаем
        if (empty($sort) || ! $emptyIfDefault) {
            return $sort;
        }

        // если не задана сортировка по-умолчанию или она многозначительная, то возвращаем
        if (! is_array($this->defaultOrder) || count($this->defaultOrder) !== 1) {
            return $sort;
        }

        // определяем аттрибут и направление сортировки
        $attr = $sort;
        $order = SORT_ASC;

        if (strncmp($attr, '-', 1) === 0) {
            $attr = (string)substr($attr, 1);
            $order = SORT_DESC;
        }

        // если текущая сортировка совпадает с сортировкой по-молчанию, то возвращаем пустую строку
        return ($this->defaultOrder[$attr] ?? null) === $order ? '' : $sort;
    }

    /**
     * Является ли текущая сортировка сортировкой по-умолчанию.
     *
     * @return bool
     */
    public function getIsDefault() : bool
    {
        $sort = $this->sort;

        // если сортировка не задана, то она по-умолчанию
        if (empty($sort)) {
            return true;
        }

        // если не задана сортировка по-умолчанию или она многозначительная
        if (! is_array($this->defaultOrder) || count($this->defaultOrder) !== 1) {
            return false;
        }

        // определяем аттрибут и направление сортировки
        $attr = $sort;
        $order = SORT_ASC;

        if (strncmp($attr, '-', 1) === 0) {
            $attr = (string)substr($attr, 1);
            $order = SORT_DESC;
        }

        // сортировка по-умолчанию совпадает с текущей
        return ($this->defaultOrder[$attr] ?? null) === $order;
    }

    /**
     * Параметры для запроса.
     *
     * @return array
     */
    public function params() : array
    {
        return $this->isDefault ? [] : [
            $this->sortParam => $this->sort
        ];
    }
}
