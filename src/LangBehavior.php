<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 28.09.20 02:34:34
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\helper\ArrayHelper;
use Locale;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

use function call_user_func;
use function is_array;
use function is_string;

/**
 * Связь объекта с описаниями на разных языках.
 *
 * Например, имеется модель товара Prod с полем id.
 *
 * Имеется языковая модель с описание товара на разных языках ProdLang с полями:
 * - prod_id (ссылка на товар по id),
 * - lang (код или id языка) и
 * - name (название товара для этого языка)
 *
 * Для связи товара с языковыми моделями определяем товару behavior таким образом:
 *
 * class Prod extends ActiveRecord
 * {
 *     public function behaviors()
 *     {
 *         return array_merge(parent::behaviors(), [
 *             'lang' => LangBehavior::class,
 *             'relationClass' => ProdLang::class,
 *             'relationLink' => ['prod_id' => 'id'],
 *             'langAttribute' => 'lang',
 *             // если в ProdLang определена связи с 'prod'
 *             'inverseOf' => 'prod'
 *        ]);
 *    }
 * }
 *
 * Также можно определить обратную связь в ProdLang
 *
 * class ProdLang extends ActiveRecord
 * {
 *     public function getProd()
 *     {
 *         return $this->hasOne(['id' => 'prod_id']);
 *     }
 * }
 *
 * Добавляет модели свойства $langs и $lang:
 * -property ActiveRecord $lang
 * -method bool setLang(ActiveRecord $lang)
 * -property ActiveRecord[] $langs
 * -method array setLangs(array $langs)
 *
 * @property ActiveRecord $lang языковая модель для текущего языка
 * @property ActiveRecord[] $langs все языковые модели
 * @property-read ActiveRecord $owner
 */
class LangBehavior extends Behavior
{
    /**
     * @var string класс языковой модели с данными языка для связи с родительским объектом.
     * Должен быть подклассом ActiveRecord.
     */
    public $relationClass;

    /**
     * @var array описание связи hasMany языковой модели с родительской, например ['brand_id' => 'id']
     */
    public $relationLink;

    /**
     * @var string поле со значением языка в языковой модели для индексации связей ActiveQuery::indexBy
     */
    public $langAttribute = 'lang';

    /** @var ?string обратная связь ActiveQuery::inverseOf */
    public $inverseOf;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init() : void
    {
        parent::init();

        if (! is_string($this->relationClass) || ! is_a($this->relationClass, ActiveRecord::class, true)) {
            throw new InvalidConfigException('relationClass должен быть экземпляром ActiveRecord');
        }

        if (empty($this->relationLink) || ! is_array($this->relationLink)) {
            throw new InvalidConfigException('relationLink должен быть массивом с описанием связи hasMany');
        }

        if (empty($this->langAttribute)) {
            throw new InvalidConfigException('langAttr пустое значение аттрибута языка в модели');
        }
    }

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
     * Возвращает код текущего языка.
     *
     * @param ?string $lang
     * @return string
     */
    public static function currentLanguage(?string $lang = null) : string
    {
        if (! isset($lang)) {
            $lang = Locale::getPrimaryLanguage(Yii::$app->language);
        }

        $matches = null;
        if (preg_match('~^(\w+)\W~um', $lang)) {
            $lang = $matches[1];
        }

        return $lang;
    }

    /**
     * Возвращает связь с языковыми описаниями.
     *
     * @return ActiveQuery
     */
    public function getLangs() : ActiveQuery
    {
        $link = $this->owner->hasMany($this->relationClass, $this->relationLink)
            ->indexBy($this->langAttribute);

        if (isset($this->inverseOf)) {
            $link->inverseOf($this->inverseOf);
        }

        return $link;
    }

    /**
     * Устанавливает связи с языками
     *
     * @param ActiveRecord[] $langs
     * @return string[] errors
     */
    public function setLangs(array $langs) : array
    {
        /** @var ActiveRecord[] $langs */
        $langs = ArrayHelper::index($langs, $this->langAttribute);

        /** @var string[] ошибки */
        $errors = [];

        // сохраняем данные в базу
        foreach ($langs as $lang) {
            // устанавливаем родительскую связь чтобы прошла проверка validate
            foreach ($this->relationLink as $langAttr => $ownerAttr) {
                $lang->setAttribute($langAttr, $this->owner->{$ownerAttr});
            }

            // новые записи вставляем методом upsert для избежания конфликтов с существующими
            $method = $lang->isNewRecord && $lang->hasMethod('upsert') ? 'upsert' : 'save';
            if ($lang->$method() === false) {
                $errors += $lang->firstErrors;
            }
        }

        // готовим критерии и удаляем лишние языки
        $conds = ['and'];
        foreach ($this->relationLink as $langAttr => $ownerAttr) {
            $conds[] = [$langAttr => $this->owner->getAttribute($ownerAttr)];
        }

        if (! empty($langs)) {
            $conds[] = ['not in', $this->langAttribute, array_keys($langs)];
        }

        // удаляем лишние
        call_user_func([$this->relationClass, 'deleteAll'], $conds);

        // обновляем кэш связей с языковыми моделями
        $this->owner->populateRelation('langs', $langs);

        // обновляем кэш связи с языковой моделью для текущего языка
        $currentLang = static::currentLanguage();
        if (isset($langs[$currentLang])) {
            $this->owner->populateRelation('lang', $langs[$currentLang]);
        } else {
            unset($this->owner->lang);
        }

        // возвращаем ошибки
        return $errors;
    }

    /**
     * Возвращает связь модели с языковой моделью для текущего языка.
     *
     * @param ?string $lang код языка, если не задан, то берется текущий из $app->language
     * @return ActiveQuery
     */
    public function getLang(?string $lang = null) : ActiveQuery
    {
        // баг в yii - не добавляется имя таблицы или алиас к полю onCondition,
        $fullName = sprintf('%s.[[%s]]', call_user_func([$this->relationClass, 'tableName']), $this->langAttribute);

        // описываем связь модели с языковой моделью для текущего языка
        $link = $this->owner->hasOne($this->relationClass, $this->relationLink)
            ->andOnCondition([$fullName => static::currentLanguage($lang)]);

        if (isset($this->inverseOf)) {
            $link->inverseOf($this->inverseOf);
        }

        return $link;
    }

    /**
     * Устанавливает языковую модель для текущего языка.
     *
     * @param ActiveRecord $lang
     * @return bool
     */
    public function setLang(ActiveRecord $lang) : bool
    {
        // код текущего языка
        $langCode = static::currentLanguage();

        // обновляем языковую модель для текущего языка
        $lang->{$this->langAttribute} = $langCode;

        // получаем список всех языков их кэша связи
        /** @noinspection PhpUndefinedFieldInspection */
        $langs = $this->owner->langs;
        $langs[$langCode] = $lang;

        // сохраняем (связь обновится при сохранении всех)
        return empty($this->setLangs($langs));
    }
}
