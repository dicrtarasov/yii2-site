<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 16.08.20 08:57:03
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
    public function init()
    {
        parent::init();

        Html::addCssClass($this->options, 'dicr-site-admin-link-pager');
    }
}
