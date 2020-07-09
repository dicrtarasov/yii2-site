<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 09.07.20 14:24:49
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\validate\ValidateException;
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
 * @property Sort $sort
 * @property Pagination $pagination
 * @property ActiveDataProvider $provider
 */
abstract class AbstractFilter extends Model
{
    /**
     * Создает новый запрос из параметров фильтра.
     *
     * @return ActiveQuery
     */
    abstract public function createQuery();

    /** @var ActiveQuery */
    protected $_query;

    /**
     * Возвращает запрос.
     *
     * @return ActiveQuery
     * @throws ValidateException
     */
    public function getQuery()
    {
        if (! isset($this->_query)) {
            if (! $this->validate()) {
                throw new ValidateException($this);
            }

            $this->_query = $this->createQuery();
        }

        return $this->_query;
    }

    /**
     * Устанавливает запрос.
     *
     * @param ActiveQuery $query
     */
    public function setQuery(ActiveQuery $query)
    {
        $this->_query = $query;
    }

    /**
     * Создает сортировку.
     *
     * @param array $config
     * @return Sort
     */
    public static function createSort(array $config = [])
    {
        return new Sort($config);
    }

    /** @var Sort */
    protected $_sort;

    /**
     * Сортировка.
     *
     * @return Sort
     */
    public function getSort()
    {
        if (! isset($this->_sort)) {
            $this->_sort = static::createSort();
        }

        return $this->_sort;
    }

    /**
     * Устанавливает сортировку.
     *
     * @param Sort|array|false $sort
     * @throws InvalidConfigException
     */
    public function setSort($sort)
    {
        if (is_array($sort)) {
            /** @noinspection OffsetOperationsInspection */
            if (! isset($sort['class'])) {
                /** @noinspection OffsetOperationsInspection */
                $sort['class'] = Sort::class;
            }

            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $sort = Yii::createObject($sort);
        } elseif (! ($sort instanceof Sort) && $sort !== false) {
            throw new InvalidConfigException('sort');
        }

        $this->_sort = $sort;
    }

    /**
     * Создает пагинацию.
     *
     * @param array $config
     * @return Pagination
     */
    public static function createPagination(array $config = [])
    {
        return new Pagination($config);
    }

    /** @var Pagination */
    protected $_pagination;

    /**
     * Пагинация.
     *
     * @return Pagination
     */
    public function getPagination()
    {
        if (! isset($this->_pagination)) {
            $this->_pagination = static::createPagination();
        }

        return $this->_pagination;
    }

    /**
     * Устанавливает пагинацию.
     *
     * @param Pagination|array|false $pagination
     * @throws InvalidConfigException
     */
    public function setPagination($pagination)
    {
        if (is_array($pagination)) {
            /** @noinspection OffsetOperationsInspection */
            if (! isset($pagination['class'])) {
                /** @noinspection OffsetOperationsInspection */
                $pagination['class'] = Pagination::class;
            }

            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $pagination = Yii::createObject($pagination);
        } elseif ((! $pagination instanceof Pagination) && $pagination !== false) {
            throw new InvalidConfigException('pagination');
        }

        $this->_pagination = $pagination;
    }

    /**
     * Создает провайдер данных.
     *
     * @param array $config
     * @return ActiveDataProvider
     */
    public static function createProvider(array $config = [])
    {
        return new ActiveDataProvider(array_merge([
            'sort' => static::createSort(),
            'pagination' => static::createPagination()
        ], $config));
    }

    /** @var ActiveDataProvider */
    protected $_provider;

    /**
     * Возвращает провайдер данных.
     *
     * @return ActiveDataProvider
     */
    public function getProvider()
    {
        if (! isset($this->_provider)) {
            $this->_provider = static::createProvider([
                'query' => $this->query,
                'sort' => $this->sort,
                'pagination' => $this->pagination
            ]);
        }

        return $this->_provider;
    }

    /**
     * Устанавливает провайдер.
     *
     * @param ActiveDataProvider|array|false $provider
     * @throws InvalidConfigException
     */
    public function setProvider($provider)
    {
        if (is_array($provider)) {
            /** @noinspection OffsetOperationsInspection */
            if (! isset($provider['class'])) {
                /** @noinspection OffsetOperationsInspection */
                $provider['class'] = ActiveDataProvider::class;
            }

            /** @noinspection OffsetOperationsInspection */
            if (! isset($provider['sort'])) {
                /** @noinspection OffsetOperationsInspection */
                $provider['sort'] = $this->sort;
            }

            /** @noinspection OffsetOperationsInspection */
            if (! isset($provider['pagination'])) {
                /** @noinspection OffsetOperationsInspection */
                $provider['pagination'] = $this->pagination;
            }

            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $provider = Yii::createObject($provider);
        } elseif ($provider !== false && (! $provider instanceof ActiveDataProvider)) {
            throw new InvalidConfigException('provider');
        }

        $this->_provider = $provider;
    }

    /**
     * Сбрасывает запрос и провайдер для последующего создания новых.
     */
    public function refresh()
    {
        $this->_query = null;
        $this->_provider = null;
    }
}

