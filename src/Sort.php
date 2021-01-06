<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 06.01.21 05:08:21
 */

declare(strict_types = 1);

namespace dicr\site;

use function array_key_first;
use function count;

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
     * @return ?string текущую сортировку 'attr' или '-attr' или null если multiSort или не задана
     */
    public function getSort(bool $emptyIfDefault = false): ?string
    {
        $current = $this->attributeOrders;
        if (empty($current)) {
            return null;
        }

        $currentSort = array_key_first($current);
        if ($current[$currentSort] ?? SORT_ASC === SORT_DESC) {
            $currentSort = '-' . $currentSort;
        }

        if ($emptyIfDefault) {
            $default = $this->defaultOrder;

            if (count($default) === 1) {
                $defaultSort = array_key_first($default);
                if ($default[$defaultSort] ?? SORT_ASC === SORT_DESC) {
                    $defaultSort = '-' . $defaultSort;
                }

                if ($currentSort === $defaultSort) {
                    $currentSort = null;
                }
            }
        }

        return $currentSort;
    }

    /**
     * Является ли текущая сортировка сортировкой по-умолчанию.
     *
     * @return bool
     */
    public function getIsDefault(): bool
    {
        return $this->getSort(true) === null;
    }

    /**
     * Параметры для запроса.
     *
     * @return array
     */
    public function params(): array
    {
        $sort = $this->getSort(true);

        return $sort === null ? [] : [
            $this->sortParam => $sort
        ];
    }
}
