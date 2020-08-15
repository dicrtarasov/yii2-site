<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 03:10:24
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
    /** @inheritDoc */
    public $sourcePath = __DIR__ . '/assets/edit-form';

    /** @inheritDoc */
    public $css = [
        'style.scss'
    ];

    /** @inheritDoc */
    public $depends = [
        AdminAsset::class,
        ToastsAsset::class,
        ActiveFormAsset::class
    ];
}
