<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 28.09.20 02:35:35
 */

declare(strict_types = 1);
namespace dicr\site;

use Throwable;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Transaction;

use function array_slice;

/**
 * Добавляет ActiveRecord метод upsert.
 * - method bool upsert($runValidation = true, $attributes = null)
 *
 * ВНИМАНИЕ!!! Необходимо использовать там где нет autogenerated id.
 *
 * @property ActiveRecord $owner
 */
class UpsertBehavior extends Behavior
{
    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function attach($owner) : void
    {
        if (! $owner instanceof ActiveRecord) {
            throw new InvalidConfigException('owner должен быть типа ActiveRecord');
        }

        parent::attach($owner);
    }

    /**
     * Upsert (INSERT on duplicate keys UPDATE)
     *
     * @param bool $runValidation
     * @param ?array $attributes
     * @return bool
     * @throws Throwable
     */
    public function upsert(bool $runValidation = true, ?array $attributes = null) : bool
    {
        if ($runValidation) {
            // reset isNewRecord to pass "unique" attribute validator because of upsert
            $this->owner->isNewRecord = false;

            if (! $this->owner->validate($attributes)) {
                Yii::warning('Model not inserted due to validation error.', __METHOD__);

                return false;
            }
        }

        if (! $this->owner->isTransactional(ActiveRecord::OP_INSERT)) {
            return $this->upsertInternal($attributes);
        }

        /** @var Transaction $transaction */
        $transaction = $this->owner::getDb()->beginTransaction();
        try {
            $result = $this->upsertInternal($attributes);
            if ($result !== false) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }

            return $result;
        } catch (Throwable $e) {
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
    protected function upsertInternal(?array $attributes = null) : bool
    {
        if (! $this->owner->beforeSave(true)) {
            return false;
        }

        // attributes for INSERT
        $insertValues = $this->owner->getAttributes($attributes);
        $db = $this->owner::getDb();
        $tableName = $this->owner::tableName();

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
        $this->owner->isNewRecord = false;

        // call handlers
        $this->owner->afterSave(true, array_fill_keys(array_keys($insertValues), null));

        return true;
    }
}
