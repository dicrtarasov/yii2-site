<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.07.20 13:37:50
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use yii\helpers\Html;

/**
 * LinkPager.
 */
class LinkPager extends \yii\bootstrap4\LinkPager
{
    /** @var bool|string */
    public $firstPageLabel = '<i class="fas fa-angle-double-left"></i>';

    /** @var string|bool */
    public $prevPageLabel = '<i class="fas fa-angle-left"></i>';

    /** @var string|bool */
    public $nextPageLabel = '<i class="fas fa-angle-right"></i>';

    /** @var bool|string */
    public $lastPageLabel = '<i class="fas fa-angle-double-right"></i>';

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        Html::addCssClass($this->options, 'dicr-admin-link-pager');
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        LinkPagerAsset::register($this->view);

        parent::run();
    }
}
