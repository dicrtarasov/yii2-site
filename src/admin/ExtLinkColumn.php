<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 00:24:08
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;
use yii\grid\DataColumn;

use function is_array;

/**
 * Колона GridView для внешних ссылок.
 */
class ExtLinkColumn extends DataColumn
{
    /** Значения стиля word-break */
    public string $wordBreak = 'break-all';

    /** @var ?string атрибут data-pjax */
    public ?string $dataPjax = '0';

    /** @var ?string аттрибут target */
    public ?string $target = '_blank';

    /**
     * {@inheritDoc}
     */
    public function init(): void
    {
        if (! is_array($this->format)) {
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
