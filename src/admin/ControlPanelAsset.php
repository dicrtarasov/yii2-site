<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.07.20 13:18:07
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use yii\web\AssetBundle;

/**
 * Ресурсы ControlPanel.
 */
class ControlPanelAsset extends AssetBundle
{
    /** @var string */
    public $sourcePath = __DIR__ . '/assets/control-panel';

    /** @var string[] */
    public $css = [
        'style.css'
    ];

    /** @var string[] */
    public $depends = [
        AdminAsset::class
    ];
}
