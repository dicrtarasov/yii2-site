<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 10.07.20 19:04:10
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use yii\web\AssetBundle;

/**
 * Ресурсы LinkPager.
 */
class LinkPagerAsset extends AssetBundle
{
    /** @inheritDoc */
    public $sourcePath = __DIR__ . '/assets/link-pager';

    /** @inheritDoc */
    public $css = [
        'style.css'
    ];

    /** @inheritDoc */
    public $depends = [
        AdminAsset::class
    ];
}
