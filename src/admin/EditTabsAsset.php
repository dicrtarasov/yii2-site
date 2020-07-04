<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.07.20 13:19:20
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use yii\web\AssetBundle;

/**
 * Ресурсы EditTabs.
 */
class EditTabsAsset extends AssetBundle
{
    /** @var string */
    public $sourcePath = __DIR__ . '/assets/edit-tabs';

    /** @var string[] */
    public $css = [
        'style.css'
    ];

    /** @var string[] */
    public $js = [
        'script.js'
    ];

    /** @var string[] */
    public $depends = [
        AdminAsset::class
    ];
}
