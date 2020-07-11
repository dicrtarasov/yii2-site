<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 11.07.20 09:53:19
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;

/**
 * Хлебные крошки.
 */
class Breadcrumbs extends \yii\bootstrap4\Breadcrumbs
{
    /** @inheritDoc */
    public $homeLink = [
        'label' => 'Главная',
        'url' => ['default/index'],
    ];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        Html::addCssClass($this->navOptions, 'dicr-admin-breadcrumbs');
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        BreadcrumbsAsset::register($this->view);

        return parent::run();
    }
}
