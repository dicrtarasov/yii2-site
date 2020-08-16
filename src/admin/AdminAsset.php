<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 08:54:53
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\site\SiteAsset;
use dicr\widgets\ToastsAsset;
use yii\grid\GridViewAsset;
use yii\web\AssetBundle;
use yii\widgets\ActiveFormAsset;

/**
 * Ресурсы админки.
 */
class AdminAsset extends AssetBundle
{
    /** @inheritDoc */
    public $sourcePath = __DIR__ . '/assets';

    /** @inheritDoc */
    public $css = [
        'admin.scss',
        'control-panel.scss',
        'edit-form.scss',
        'edit-tabs.scss',
        'grid-view.scss',
        'link-pager.scss',
        'navbar.scss'
    ];

    /** @inheritDoc */
    public $js = [
        'edit-tabs.js'
    ];

    /** @inheritDoc */
    public $depends = [
        SiteAsset::class,
        ToastsAsset::class,
        ActiveFormAsset::class,
        GridViewAsset::class
    ];
}
