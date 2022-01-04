<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.01.22 23:15:05
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\helper\Url;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\QueryInterface;

use function is_array;

/**
 * Базовый фильтр.
 *
 * @property QueryInterface $query SQL-запрос
 * @property ?Sort $sort
 * @property ?Pagination $pagination
 * @property ActiveDataProvider $provider
 * @property-read array $params параметры запроса
 */
abstract class AbstractFilter extends Model
{
    protected ?QueryInterface $_query = null;

    /**
     * Создает новый запрос из параметров фильтра.
     */
    abstract public function createQuery(): QueryInterface;

    /**
     * Возвращает запрос.
     */
    public function getQuery(): QueryInterface
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
     * @return $this
     */
    public function setQuery(QueryInterface $query): self
    {
        $this->_query = $query;

        return $this;
    }

    protected Sort|false $_sort;

    /**
     * Создает сортировку.
     *
     * @throws InvalidConfigException
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function createSort(array $config = []): ?Sort
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject($config + ['class' => Sort::class]);
    }

    /**
     * Сортировка.
     *
     * @throws InvalidConfigException
     */
    public function getSort(): ?Sort
    {
        if (! isset($this->_sort)) {
            $this->_sort = $this->createSort() ?: false;
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
    public function setSort(Sort|array|false $sort): self
    {
        if (is_array($sort)) {
            $sort = $this->createSort($sort);
        } elseif (! ($sort instanceof Sort) && $sort !== false) {
            throw new InvalidConfigException('sort');
        }

        $this->_sort = $sort;

        return $this;
    }

    protected Pagination|false $_pagination;

    /**
     * Создает пагинацию.
     *
     * @throws InvalidConfigException
     * @noinspection PhpMethodMayBeStaticInspection
     */
    public function createPagination(array $config = []): ?Pagination
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject($config + ['class' => Pagination::class]);
    }

    /**
     * Пагинация.
     *
     * @throws InvalidConfigException
     */
    public function getPagination(): ?Pagination
    {
        if (! isset($this->_pagination)) {
            $this->_pagination = $this->createPagination() ?: false;
        }

        return $this->_pagination ?: null;
    }

    /**
     * Устанавливает пагинацию.
     *
     * @return $this
     * @throws InvalidConfigException
     */
    public function setPagination(Pagination|array|false $pagination): self
    {
        if (is_array($pagination)) {
            $pagination = $this->createPagination($pagination);
        } elseif ((! $pagination instanceof Pagination) && $pagination !== false) {
            throw new InvalidConfigException('pagination');
        }

        $this->_pagination = $pagination;

        return $this;
    }

    protected ?ActiveDataProvider $_provider = null;

    /**
     * Создает провайдер данных.
     *
     * @throws InvalidConfigException
     */
    public function createProvider(array $config = []): ActiveDataProvider
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject($config + [
                'class' => ActiveDataProvider::class,
                'query' => $this->getQuery(),
                'sort' => $this->getSort() ?: false,
                'pagination' => $this->getPagination() ?: false
            ]);
    }

    /**
     * Возвращает провайдер данных.
     *
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
     * @return $this
     * @throws InvalidConfigException
     */
    public function setProvider(ActiveDataProvider|array $provider): self
    {
        if (is_array($provider)) {
            $provider = $this->createProvider($provider);
        } elseif (! $provider instanceof ActiveDataProvider) {
            throw new InvalidConfigException('provider');
        }

        $this->_provider = $provider;
        $this->_query = $provider->query;
        $this->_sort = $provider->getSort() ?: false;
        $this->_pagination = $provider->getPagination() ?: false;

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
     * Параметры фильтра.
     *
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
     * @see params()
     */
    public function getParams(): array
    {
        return $this->params();
    }
}
