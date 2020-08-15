<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 03:00:48
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\asset\FontAwesomeAsset;
use yii\bootstrap4\BootstrapAsset;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Стандартный набор ресурсов сайта.
 */
class SiteAsset extends AssetBundle
{
    /** @inheritDoc */
    public $sourcePath = __DIR__ . '/assets/site';

    /** @inheritDoc */
    public $depends = [
        JqueryAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        FontAwesomeAsset::class
    ];
}
