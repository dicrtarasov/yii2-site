<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.08.21 22:26:24
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;

/**
 * LinkPager
 */
class LinkPager extends \yii\bootstrap5\LinkPager
{
    /** @inheritDoc */
    public $firstPageLabel = '<i class="fas fa-angle-double-left"></i>';

    /** @inheritDoc */
    public $prevPageLabel = '<i class="fas fa-angle-left"></i>';

    /** @inheritDoc */
    public $nextPageLabel = '<i class="fas fa-angle-right"></i>';

    /** @inheritDoc */
    public $lastPageLabel = '<i class="fas fa-angle-double-right"></i>';

    /**
     * @inheritDoc
     */
    public function init() : void
    {
        parent::init();

        Html::addCssClass($this->options, 'dicr-site-admin-link-pager');
    }

    /**
     * @inheritDoc
     */
    public function run() : string
    {
        AdminAsset::register($this->view);

        return parent::run();
    }
}
