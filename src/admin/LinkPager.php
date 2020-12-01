<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 02.12.20 03:08:17
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;

/**
 * LinkPager
 */
class LinkPager extends \yii\bootstrap4\LinkPager
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

        return (string)parent::run();
    }
}
