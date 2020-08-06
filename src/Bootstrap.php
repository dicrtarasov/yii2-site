<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 07.08.20 00:01:03
 */

declare(strict_types = 1);

namespace dicr\site;

use Yii;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

/**
 * Автозагрузка при настройке пакета.
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritDoc
     */
    public function bootstrap($app) : void
    {
        // Трансляция
        $app->i18n->translations['dicr/site'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'ru',
            'basePath' => __DIR__ . '/messages'
        ];

        // заменя классов
        Yii::$container->set(\yii\db\ActiveRecord::class, ActiveRecord::class);
        Yii::$container->set(\yii\data\Sort::class, Sort::class);
        Yii::$container->set(\yii\data\Pagination::class, Pagination::class);
    }
}
