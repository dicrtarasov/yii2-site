<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.07.20 13:18:34
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\widgets\ToastsAsset;
use yii\web\AssetBundle;
use yii\widgets\ActiveFormAsset;

/**
 * Ресурсы EditForm.
 */
class EditFormAsset extends AssetBundle
{
    /** @var string */
    public $sourcePath = __DIR__ . '/assets/edit-form';

    /** @var string[] */
    public $css = [
        'style.css'
    ];

    /** @var string[] */
    public $depends = [
        AdminAsset::class,
        ToastsAsset::class,
        ActiveFormAsset::class
    ];
}
