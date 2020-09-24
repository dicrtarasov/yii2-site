<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 25.09.20 01:14:11
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;
use dicr\widgets\Widget;

use function array_merge;

/**
 * Панель управления для панели навигации.
 */
class ControlPanel extends Widget
{
    /** @var array url для кнопки создания */
    public $create;

    /** @var array url для кнопки удаления */
    public $remove;

    /** @var array опции кнопки сохранить (form) */
    public $submit;

    /** @var array url кнопки скачивания, ex. Url::current([export => 1]) */
    public $download;

    /** @var string[] дополнительные кнопки */
    public $buttons;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $this->buttons = empty($this->buttons) ? [] : (array)$this->buttons;

        Html::addCssClass($this->options, 'dicr-site-admin-control-panel');
    }

    /**
     * Создает кнопки
     *
     * @return string[]
     */
    protected function createButtons(): array
    {
        $buttons = [];

        if (! empty($this->create)) {
            $buttons['create'] = Html::a(Html::fas('plus-square'), $this->create, [
                'class' => 'btn btn-sm btn-success',
                'encode' => false,
                'title' => 'Создать'
            ]);
        }

        if (! empty($this->submit)) {
            $options = $this->submit + ['title' => 'Сохранить'];
            Html::addCssClass($options, ['btn btn-sm btn-primary']);
            $buttons['submit'] = Html::submitButton(Html::fas('save'), $options);
        }

        if (! empty($this->download)) {
            $buttons['download'] = Html::a(Html::fas('download'), $this->download, [
                'class' => 'btn btn-sm btn-secondary',
                'encode' => false,
                'title' => 'Скачать'
            ]);
        }

        if (! empty($this->buttons)) {
            $buttons = array_merge($buttons, $this->buttons);
        }

        if (! empty($this->remove)) {
            $buttons['remove'] = Html::a(Html::fas('trash-alt'), $this->remove, [
                'class' => 'btn btn-sm btn-danger',
                'encode' => false,
                'title' => 'Удалить',
                'onclick' => 'return confirm(\'Удалить?\')'
            ]);
        }

        return $buttons;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $buttons = $this->createButtons();
        if (empty($buttons)) {
            return '';
        }

        ob_start();
        echo Html::beginTag('section', $this->options);

        foreach ($buttons as $button) {
            echo $button;
        }

        echo Html::endTag('section');

        return ob_get_clean();
    }
}
