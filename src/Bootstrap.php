<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 13.08.21 23:39:56
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
    public function bootstrap($app): void
    {
        // Трансляция
        $app->i18n->translations['dicr/site'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'ru',
            'basePath' => __DIR__ . '/messages'
        ];

        // заменя классов
        Yii::$container->set(\yii\db\ActiveRecord::class, ActiveRecord::class);
        Yii::$container->set(\yii\i18n\Formatter::class, Formatter::class);
        Yii::$container->set(\yii\data\Pagination::class, Pagination::class);
        Yii::$container->set(\yii\data\Sort::class, Sort::class);
    }
}
