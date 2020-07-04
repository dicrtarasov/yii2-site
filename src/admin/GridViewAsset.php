<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.07.20 13:20:05
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use yii\web\AssetBundle;

/**
 * Ресурсы GridView.
 */
class GridViewAsset extends AssetBundle
{
    /** @var string */
    public $sourcePath = __DIR__ . '/assets/grid-view';

    /** @var string[] */
    public $css = [
        'style.css'
    ];

    /** @var string[] */
    public $depends = [
        AdminAsset::class,
        \yii\grid\GridViewAsset::class,
    ];
}
