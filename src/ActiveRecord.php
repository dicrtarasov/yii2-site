<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 11.03.21 20:20:45
 */

declare(strict_types = 1);
namespace dicr\site;

use Yii;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;
use yii\db\Exception;
use yii\db\Transaction;

use function array_fill_keys;
use function array_keys;
use function array_slice;

/**
 * Базовая модель с добавлением функций upsert и cache.
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritDoc
     */
    public function load($data, $formName = null): bool
    {
        $ret = parent::load($data, $formName);

        // если имеется FileAttributeBehavior, загружаем файлы
        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->hasMethod('loadFileAttributes') && $this->loadFileAttributes($formName)) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        // insert вызывает только afterSave(true)
        // update вызывает updateAll
        // delete вызывает deleteAll
        if ($insert && ! empty($changedAttributes)) {
            static::invalidateClassCache();
        }
    }

    /**
     * @inheritDoc
     */
    public static function updateAll($attributes, $condition = '', $params = []): int
    {
        $ret = parent::updateAll($attributes, $condition, $params);

        if ($ret) {
            static::invalidateClassCache();
        }

        return $ret;
    }

    /**
     * @inheritDoc
     */
    public static function updateAllCounters($counters, $condition = '', $params = []): int
    {
        $ret = parent::updateAllCounters($counters, $condition, $params);

        if ($ret) {
            static::invalidateClassCache();
        }

        return $ret;
    }

    /**
     * @inheritDoc
     */
    public static function deleteAll($condition = null, $params = []): int
    {
        $ret = parent::deleteAll($condition, $params);

        if ($ret) {
            static::invalidateClassCache();
        }

        return $ret;
    }

    /**
     * Аннулировать кэш модели.
     */
    public static function invalidateClassCache(): void
    {
        if (! empty(Yii::$app->cache)) {
            TagDependency::invalidate(Yii::$app->cache, static::class);
        }
    }

    /**
     * Создает и загружает массив моделей из табулярных данных.
     *
     * Чтобы каждый раз при сохранении не удалять/пересоздавать все табулярные модели заново,
     * можно использовать уже существующие в базе, для обновления при помощи save().
     *
     * В $current передается массив существующих в базе моделей для загрузки. Этот массив должен быть индексирован по
     * такому же ключу как и данные формы.
     *
     * В $data[$formName] передается массив данных отправленных моделей, индексированный по ключу-идентификатору
     * модели. Если модель с таким ключом отсутствует в массиве существующих ($models), то создается новая.
     *
     * Если $current не задан, то все модели будут созданы из данных.
     *
     * Модели из $current, ключ которых отсутствует в данных формы не возвращаются.
     *
     * @param array $currentModels
     * @param array $data табулярные данные, например из $_POST
     * @param ?string $formName
     * @return static[]
     * @throws InvalidConfigException
     */
    public static function loadAll(array $currentModels, array $data, ?string $formName = null): array
    {
        if (empty($currentModels)) {
            $currentModels = [];
        }

        if (! isset($formName)) {
            $formName = static::instance()->formName();
        }

        // корректируем данные под форму
        if ($formName !== '') {
            $data = $data[$formName] ?? [];
        }

        $models = [];
        foreach ($data as $key => $modelData) {
            $model = $currentModels[$key] ?? new static();
            $model->load($modelData, '');
            $models[$key] = $model;
        }

        return $models;
    }

    /**
     * Upsert (INSERT on duplicate keys UPDATE)
     *
     * @param bool $runValidation
     * @param ?array $attributes
     * @return bool
     * @throws Exception
     */
    public function upsert(bool $runValidation = true, ?array $attributes = null): bool
    {
        if ($runValidation) {
            // reset isNewRecord to pass "unique" attribute validator because of upsert
            $this->isNewRecord = false;

            if (! $this->validate($attributes)) {
                Yii::warning('Model not inserted due to validation error.', __METHOD__);

                return false;
            }
        }

        if (! $this->isTransactional(\yii\db\ActiveRecord::OP_INSERT)) {
            return $this->upsertInternal($attributes);
        }

        /** @var Transaction $transaction */
        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->upsertInternal($attributes);
            if ($result !== false) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }

            return $result;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Insert or update record.
     *
     * @param ?array $attributes
     * @return bool
     * @throws Exception
     */
    protected function upsertInternal(?array $attributes = null): bool
    {
        if (! $this->beforeSave(true)) {
            return false;
        }

        // attributes for INSERT
        $insertValues = $this->getAttributes($attributes);
        $db = static::getDb();
        $tableName = static::tableName();

        // attributes for UPDATE exclude primaryKey
        $updateValues = array_slice($insertValues, 0);
        foreach ($db->getTableSchema($tableName)->primaryKey as $key) {
            unset($updateValues[$key]);
        }

        // process update/insert
        $cmd = $db->createCommand()
            ->upsert($tableName, $insertValues, $updateValues ?: false);

        if ($cmd->execute() === false) {
            return false;
        }

        // reset isNewRecord after save
        $this->isNewRecord = false;

        // call handlers
        $this->afterSave(true, array_fill_keys(array_keys($insertValues), null));

        return true;
    }
}
