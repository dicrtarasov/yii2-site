<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 10.07.20 18:55:17
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
    public function getIsPageDefault()
    {
        return empty($this->page);
    }

    /**
     * Проверяет является ли текущий размер страницы размером по-умолчанию.
     *
     * @return bool
     */
    public function getIsPageSizeDefault()
    {
        return empty($this->pageSize) || (int)$this->pageSize === (int)$this->defaultPageSize;
    }

    /**
     * Является ли страница и размер страницы по-умолчанию.
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isPageDefault && $this->isPageSizeDefault;
    }
}
