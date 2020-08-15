<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 03:05:18
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\site\SiteAsset;
use yii\web\AssetBundle;

/**
 * Ресурсы админки.
 */
class AdminAsset extends AssetBundle
{
    /** @inheritDoc */
    public $sourcePath = __DIR__ . '/assets/admin';

    /** @inheritDoc */
    public $css = [
        'style.scss'
    ];

    /** @inheritDoc */
    public $depends = [
        SiteAsset::class
    ];
}
