<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 07.08.20 02:22:18
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\helper\Url;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use function array_merge;
use function is_array;

/**
 * Базовый фильтр.
 *
 * @property ActiveQuery $query SQL-запрос
 * @property ?Sort $sort
 * @property ?Pagination $pagination
 * @property ActiveDataProvider $provider
 */
abstract class AbstractFilter extends Model
{
    /**
     * Создает новый запрос из параметров фильтра.
     *
     * @return ActiveQuery
     */
    abstract public function createQuery() : ActiveQuery;

    /** @var ActiveQuery */
    protected $_query;

    /**
     * Возвращает запрос.
     *
     * @return ActiveQuery
     */
    public function getQuery() : ActiveQuery
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
    public function setQuery(ActiveQuery $query) : self
    {
        $this->_query = $query;
        return $this;
    }

    /**
     * Создает сортировку.
     *
     * @param array $config
     * @return Sort
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function createSort(array $config = []) : Sort
    {
        return new Sort($config);
    }

    /** @var Sort|false */
    protected $_sort;

    /**
     * Сортировка.
     *
     * @return ?Sort
     */
    public function getSort() : ?Sort
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
    public function setSort($sort) : self
    {
        if (is_array($sort)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $sort = Yii::createObject(array_merge([
                'class' => Sort::class
            ], $sort));
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
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function createPagination(array $config = []) : Pagination
    {
        return new Pagination($config);
    }

    /** @var Pagination|false */
    protected $_pagination;

    /**
     * Пагинация.
     *
     * @return ?Pagination
     */
    public function getPagination() : ?Pagination
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
    public function setPagination($pagination) : self
    {
        if (is_array($pagination)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $pagination = Yii::createObject([
                    'class' => Pagination::class
                ] + $pagination);
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
     */
    public function createProvider(array $config = []) : ActiveDataProvider
    {
        return new ActiveDataProvider([
                'query' => $this->query,
                'sort' => $this->sort ?: false,
                'pagination' => $this->pagination ?: false
            ] + $config);
    }

    /** @var ActiveDataProvider */
    protected $_provider;

    /**
     * Возвращает провайдер данных.
     *
     * @return ActiveDataProvider
     */
    public function getProvider() : ActiveDataProvider
    {
        if (! isset($this->_provider)) {
            $this->_provider = $this->createProvider();
        }

        return $this->_provider;
    }

    /**
     * Устанавливает провайдер.
     *
     * @param ActiveDataProvider|array|false $provider
     * @return $this
     * @throws InvalidConfigException
     */
    public function setProvider($provider) : self
    {
        if (is_array($provider)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $provider = Yii::createObject([
                    'class' => ActiveDataProvider::class,
                    'query' => $this->query,
                    'sort' => $this->sort ?: false,
                    'pagination' => $this->pagination ?: false
                ] + $provider);
        } elseif ($provider !== false && (! $provider instanceof ActiveDataProvider)) {
            throw new InvalidConfigException('provider');
        }

        $this->_provider = $provider;
        return $this;
    }

    /**
     * Сбрасывает запрос и провайдер для последующего создания новых.
     *
     * @return $this
     */
    public function refresh() : self
    {
        $this->_query = null;
        $this->_provider = null;
        return $this;
    }

    /**
     * Параметры фильтра.
     *
     * @return array
     */
    public function params() : array
    {
        return Url::filterQuery($this->attributes);
    }
}
