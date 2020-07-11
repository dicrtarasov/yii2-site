<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 11.07.20 09:53:44
 */
declare(strict_types = 1);
namespace dicr\site\admin;

use yii\web\AssetBundle;

/**
 * Ресурсы ControlPanel.
 */
class ControlPanelAsset extends AssetBundle
{
    /** @inheritDoc */
    public $sourcePath = __DIR__ . '/assets/control-panel';

    /** @inheritDoc */
    public $css = [
        'style.css'
    ];

    /** @inheritDoc */
    public $depends = [
        AdminAsset::class
    ];
}
