<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 03:11:30
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use yii\web\AssetBundle;

/**
 * Ресурсы EditTabs.
 */
class EditTabsAsset extends AssetBundle
{
    /** @inheritDoc */
    public $sourcePath = __DIR__ . '/assets/edit-tabs';

    /** @inheritDoc */
    public $css = [
        'style.scss'
    ];

    /** @inheritDoc */
    public $js = [
        'script.js'
    ];

    /** @inheritDoc */
    public $depends = [
        AdminAsset::class
    ];
}
