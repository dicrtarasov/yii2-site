<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 03:12:58
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use yii\web\AssetBundle;

/**
 * Ресурсы GridView.
 */
class GridViewAsset extends AssetBundle
{
    /** @inheritDoc */
    public $sourcePath = __DIR__ . '/assets/grid-view';

    /** @inheritDoc */
    public $css = [
        'style.scss'
    ];

    /** @inheritDoc */
    public $depends = [
        AdminAsset::class,
        \yii\grid\GridViewAsset::class,
    ];
}
