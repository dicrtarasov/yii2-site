<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 09.10.20 18:32:01
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;
use yii\grid\DataColumn;

/**
 * Колона GridView для внешних ссылок.
 */
class ExtLinkColumn extends DataColumn
{
    /** Значения стиля word-break */
    public $wordBreak = 'break-all';

    /** Атрибут data-pjax */
    public $dataPjax = 0;

    /** Аттрибут target */
    public $target = '_blank';

    /**
     * {@inheritDoc}
     */
    public function init() : void
    {
        if (empty($this->format)) {
            $this->format = [];
        }

        if (empty($this->format[0])) {
            $this->format[0] = 'url';
        }

        if (empty($this->format[1])) {
            $this->format[1] = [];
        }

        Html::addCssClass($this->format[1], 'ext-link');

        if (isset($this->wordBreak)) {
            Html::addCssStyle($this->format[1], ['wordBreak' => $this->wordBreak]);
        }

        if (isset($this->dataPjax)) {
            /** @noinspection UnsupportedStringOffsetOperationsInspection */
            $this->format[1]['data']['pjax'] = $this->dataPjax;
        }

        if (isset($this->target)) {
            /** @noinspection UnsupportedStringOffsetOperationsInspection */
            $this->format[1]['target'] = $this->target;
        }

        parent::init();
    }
}
