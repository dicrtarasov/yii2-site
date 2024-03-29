<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 22:51:16
 */

declare(strict_types = 1);

namespace dicr\site;

use Yii;
use yii\web\Request;

use function array_key_first;
use function mb_substr;
use function reset;

use const SORT_ASC;
use const SORT_DESC;

/**
 * Class Sort
 *
 * @property ?string $sort текущее значение сортировки (один параметр)
 * @property ?string $defaultSort сортировка по-умолчанию (одним параметром)
 * @property-read bool $isDefault является ли сортировка по-умолчанию
 */
class Sort extends \yii\data\Sort
{
    private string|false $_defaultSort;

    /**
     * Сортировка по-умолчанию.
     * (в отличии от defaultOrder поддерживает только одну сортировку
     *
     * @return ?string название сортировки по-умолчанию или null, если не задано.
     * При сортировке по-убыванию добавляется "-"
     */
    public function getDefaultSort(): ?string
    {
        if (! isset($this->_defaultSort)) {
            $defaultOrder = $this->defaultOrder;

            if (! empty($defaultOrder)) {
                $defaultSort = array_key_first($defaultOrder);

                if ($defaultOrder[$defaultSort] === SORT_DESC) {
                    $defaultSort = '-' . $defaultSort;
                }

                $this->_defaultSort = $defaultSort;
            }

            if (! isset($this->_defaultSort)) {
                $this->_defaultSort = false;
            }
        }

        return $this->_defaultSort ?: null;
    }

    /**
     * Устанавливает сортировку по-умолчанию.
     *
     * @param string $sort название сортировки (для по-убыванию начинается с "-")
     */
    public function setDefaultSort(string $sort): static
    {
        $this->_defaultSort = $sort;

        // также изменяем defaultOrder
        $order = SORT_ASC;
        if ($sort[0] === '-') {
            $order = SORT_DESC;
            $sort = mb_substr($sort, 1);
        }

        $this->defaultOrder = [
            $sort => $order
        ];

        return $this;
    }

    private string|false $_sort;

    /**
     * Возвращает текущее значение параметра сортировки.
     *
     * @param bool $emptyIfDefault возвратить пустую строку если сортировка по-умолчанию
     * @return ?string текущую сортировку 'attr' или '-attr' или null если multiSort или не задана
     */
    public function getSort(bool $emptyIfDefault = false): ?string
    {
        if (! isset($this->_sort)) {
            if (! empty($this->sortParam)) {
                $params = $this->params;
                if ($params === null) {
                    $params = Yii::$app->request instanceof Request ? Yii::$app->request->queryParams : [];
                }

                if (! empty($params[$this->sortParam])) {
                    $currentSort = $this->parseSortParam($params[$this->sortParam]);
                    if (! empty($currentSort)) {
                        $this->_sort = reset($currentSort);
                    }
                }
            }

            if (empty($this->_sort)) {
                $this->_sort = $this->defaultSort ?: false;
            }
        }

        return $emptyIfDefault && $this->_sort === $this->defaultSort ? null : $this->_sort;
    }

    /**
     * Устанавливает текущую сортировку.
     */
    public function setSort(?string $sort): static
    {
        $this->_sort = $sort ?? false;

        $this->params = [
            $this->sortParam => $sort
        ];

        return $this;
    }

    /**
     * Является ли текущая сортировка сортировкой по-умолчанию.
     */
    public function getIsDefault(): bool
    {
        return empty($this->sort) || $this->sort === $this->defaultSort;
    }

    /**
     * Параметры для запроса.
     */
    public function params(): array
    {
        $sort = $this->getSort(true);

        return $sort === null ? [] : [
            $this->sortParam => $sort
        ];
    }
}
