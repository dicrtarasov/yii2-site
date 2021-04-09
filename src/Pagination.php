<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 09.04.21 09:36:57
 */

declare(strict_types = 1);
namespace dicr\site;

/**
 * Class Pagination
 *
 * @property-read bool $isPageDefault является ли страница страницей по-умолчанию
 * @property-read bool $isPageSizeDefault является ли текущий размер страницы размером по-умолчанию
 * @property-read bool $isDefault является ли страница и размер страницы по-умолчанию
 */
class Pagination extends \yii\data\Pagination
{
    /** @inheritDoc */
    public $pageSizeParam = 'limit';

    /** @inheritDoc */
    public $pageSizeLimit = [1, 100];

    /** @inheritDoc */
    public $validatePage = false;

    /** @inheritDoc */
    public $forcePageParam = false;

    /**
     * Является ли текущая страница страницей по-умолчанию.
     *
     * @return bool
     */
    public function getIsPageDefault() : bool
    {
        return empty($this->page);
    }

    /**
     * Проверяет является ли текущий размер страницы размером по-умолчанию.
     *
     * @return bool
     */
    public function getIsPageSizeDefault() : bool
    {
        return empty($this->pageSize) || $this->pageSize === $this->defaultPageSize;
    }

    /**
     * Является ли страница и размер страницы по-умолчанию.
     *
     * @return bool
     */
    public function getIsDefault() : bool
    {
        return $this->isPageDefault && $this->isPageSizeDefault;
    }

    /**
     * Возвращает параметры запроса.
     *
     * @return array
     */
    public function params() : array
    {
        return array_filter([
            $this->pageParam => $this->isPageDefault ? null : $this->page + 1,
            $this->pageSizeParam => $this->isPageSizeDefault ? null : $this->pageSize
        ], static fn($val): bool => $val !== null && $val !== '');
    }
}
