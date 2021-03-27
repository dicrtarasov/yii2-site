<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 27.03.21 22:02:41
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\helper\Url;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

use function is_array;

/**
 * Базовый фильтр.
 *
 * @property ActiveQuery $query SQL-запрос
 * @property ?Sort $sort
 * @property ?Pagination $pagination
 * @property ActiveDataProvider $provider
 * @property-read array $params параметры запроса
 */
abstract class AbstractFilter extends Model
{
    /**
     * Создает новый запрос из параметров фильтра.
     *
     * @return ActiveQuery
     */
    abstract public function createQuery(): ActiveQuery;

    /** @var ActiveQuery */
    protected $_query;

    /**
     * Возвращает запрос.
     *
     * @return ActiveQuery
     */
    public function getQuery(): ActiveQuery
    {
        if (! isset($this->_query)) {
            $this->_query = $this->createQuery();

            if (! $this->validate()) {
                $this->_query->where('0');
            }
        }

        return $this->_query;
    }

    /**
     * Устанавливает запрос.
     *
     * @param ActiveQuery $query
     * @return $this
     */
    public function setQuery(ActiveQuery $query): self
    {
        $this->_query = $query;

        return $this;
    }

    /**
     * Создает сортировку.
     *
     * @param array $config
     * @return Sort
     * @throws InvalidConfigException
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function createSort(array $config = []): Sort
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject($config + ['class' => Sort::class]);
    }

    /** @var Sort|false */
    protected $_sort;

    /**
     * Сортировка.
     *
     * @return ?Sort
     * @throws InvalidConfigException
     */
    public function getSort(): ?Sort
    {
        if (! isset($this->_sort)) {
            $this->_sort = $this->createSort();
        }

        return $this->_sort ?: null;
    }

    /**
     * Устанавливает сортировку.
     *
     * @param Sort|array|false $sort
     * @return $this
     * @throws InvalidConfigException
     */
    public function setSort($sort): self
    {
        if (is_array($sort)) {
            $sort = $this->createSort($sort);
        } elseif (! ($sort instanceof Sort) && $sort !== false) {
            throw new InvalidConfigException('sort');
        }

        $this->_sort = $sort;

        return $this;
    }

    /**
     * Создает пагинацию.
     *
     * @param array $config
     * @return Pagination
     * @throws InvalidConfigException
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function createPagination(array $config = []): Pagination
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject($config + ['class' => Pagination::class]);
    }

    /** @var Pagination|false */
    protected $_pagination;

    /**
     * Пагинация.
     *
     * @return ?Pagination
     * @throws InvalidConfigException
     */
    public function getPagination(): ?Pagination
    {
        if (! isset($this->_pagination)) {
            $this->_pagination = $this->createPagination();
        }

        return $this->_pagination ?: null;
    }

    /**
     * Устанавливает пагинацию.
     *
     * @param Pagination|array|false $pagination
     * @return $this
     * @throws InvalidConfigException
     */
    public function setPagination($pagination): self
    {
        if (is_array($pagination)) {
            $pagination = $this->createPagination($pagination);
        } elseif ((! $pagination instanceof Pagination) && $pagination !== false) {
            throw new InvalidConfigException('pagination');
        }

        $this->_pagination = $pagination;

        return $this;
    }

    /**
     * Создает провайдер данных.
     *
     * @param array $config
     * @return ActiveDataProvider
     * @throws InvalidConfigException
     */
    public function createProvider(array $config = []): ActiveDataProvider
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject($config + [
                'class' => ActiveDataProvider::class,
                'query' => $this->query,
                'sort' => $this->sort ?: false,
                'pagination' => $this->pagination ?: false
            ]);
    }

    /** @var ActiveDataProvider */
    protected $_provider;

    /**
     * Возвращает провайдер данных.
     *
     * @return ActiveDataProvider
     * @throws InvalidConfigException
     */
    public function getProvider(): ActiveDataProvider
    {
        if (! isset($this->_provider)) {
            $this->_provider = $this->createProvider();
        }

        return $this->_provider;
    }

    /**
     * Устанавливает провайдер.
     *
     * @param ActiveDataProvider|array $provider
     * @return $this
     * @throws InvalidConfigException
     */
    public function setProvider($provider): self
    {
        if (is_array($provider)) {
            $provider = $this->createProvider($provider);
        } elseif (! $provider instanceof ActiveDataProvider) {
            throw new InvalidConfigException('provider');
        }

        $this->_provider = $provider;
        $this->_query = $provider->query;
        $this->_sort = $provider->sort;
        $this->_pagination = $provider->pagination;

        return $this;
    }

    /**
     * Сбрасывает запрос и провайдер для последующего создания новых.
     *
     * @return $this
     */
    public function refresh(): self
    {
        $this->_query = null;
        $this->_provider = null;

        return $this;
    }

    /**
     * @return array
     */
    public function params(): array
    {
        $params = $this->getAttributes($this->safeAttributes());

        return Url::normalizeQuery(Url::filterQuery($params));
    }

    /**
     * Параметры фильтра.
     *
     * @return array
     * @see params()
     */
    public function getParams(): array
    {
        return $this->params();
    }
}
