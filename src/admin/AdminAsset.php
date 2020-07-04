<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.07.20 13:16:50
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\asset\FontAwesomeAsset;
use yii\bootstrap4\BootstrapAsset;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Ресурсы админки.
 *
 * @noinspection PhpUnused
 */
class AdminAsset extends AssetBundle
{
    /** @var string */
    public $sourcePath = __DIR__ . '/assets/admin';

    /** @var string[] */
    public $css = [
        'style.css'
    ];

    /** @var string[] */
    public $depends = [
        JqueryAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        FontAwesomeAsset::class
    ];
}
