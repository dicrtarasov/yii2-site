<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 09.07.20 14:25:21
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\widgets\Widget;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

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

        Html::addCssClass($this->options, 'dicr-admin-control-panel');
    }

    /**
     * Создает кнопки
     *
     * @return string[]
     */
    protected function createButtons()
    {
        $buttons = [];

        if (! empty($this->create)) {
            $buttons['create'] = Html::a('<i class="fas fa-plus-square"></i>', $this->create, [
                'class' => 'btn btn-sm btn-success',
                'encode' => false,
                'title' => 'Создать'
            ]);
        }

        if (! empty($this->remove)) {
            $buttons['remove'] = Html::a('<i class="fas fa-trash-alt"></i>', $this->remove, [
                'class' => 'btn btn-sm btn-danger',
                'encode' => false,
                'title' => 'Удалить',
                'onclick' => 'return confirm(\'Удалить?\')'
            ]);
        }

        if (! empty($this->submit)) {
            $options = ArrayHelper::merge(['title' => 'Сохранить'], $this->submit);
            Html::addCssClass($options, ['btn btn-sm btn-primary']);
            $buttons['submit'] = Html::submitButton('<i class="fas fa-save"></i>', $options);
        }

        if (! empty($this->download)) {
            $buttons['download'] = Html::a('<i class="fas fa-download"></i>', $this->download, [
                'class' => 'btn btn-sm btn-secondary',
                'encode' => false,
                'title' => 'Скачать'
            ]);
        }

        if (! empty($this->buttons)) {
            $buttons = array_merge($buttons, $this->buttons);
        }

        return $buttons;
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function run()
    {
        $buttons = $this->createButtons();
        if (empty($buttons)) {
            return '';
        }

        $this->view->registerAssetBundle(ControlPanelAsset::class);

        ob_start();
        echo Html::beginTag('section', $this->options);

        foreach ($buttons as $button) {
            echo $button;
        }

        echo Html::endTag('section');
        return ob_get_clean();
    }
}
