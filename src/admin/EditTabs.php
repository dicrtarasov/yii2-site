<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 22:54:44
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\Html;
use yii\bootstrap5\Nav;

use function is_string;
use function ob_get_clean;
use function trim;

/**
 * Табы редактора.
 *
 * Расширяет класс \yii\bootstrap\Nav, добавляя возможность указывать элементы в виде:
 * target => label
 *
 * Также добавляет tab-content в конце, поэтому можно использовать через begin/end
 *
 * ```php
 * EditTabs::begin([
 *  'items' => [
 *    'tab-main' => 'Основные',
 *    'tab-attr' => 'Характеристики'
 * ]);
 *
 * EditTabs::beginTab('tab-main', true);
 * // содержимое основной кладки
 * EditTabs::endTab();
 *
 * EditTabs::beginTab('tab-attrs');
 * // содержимое дополнительной вкладки
 * EditTabs::endTab();
 *
 * EditTabs::end(); // конец виджета
 * ```
 */
class EditTabs extends Nav
{
    /**
     * @inheritDoc
     */
    public function init(): void
    {
        // корректируем элементы
        $this->adjustItems();

        parent::init();

        Html::addCssClass($this->options, ['nav-tabs', 'dicr-site-admin-edit-tabs']);

        ob_start();
    }

    /**
     * @inheritDoc
     */
    public function run(): string
    {
        $content = trim(ob_get_clean());

        AdminAsset::register($this->view);

        $this->registerPlugin('dicrSiteAdminEditTabs');

        // вкладки навигации
        ob_start();
        $html = parent::run();
        $html .= ob_get_clean();

        // если виджет использовался не в режиме begin/end, то просто выводим html код ссылок
        if ($content === '') {
            return $html;
        }

        // выводим полноценный виджет
        ob_start();
        echo $html; // ссылки табов

        static::beginTabContent();
        echo $content;  // содержимое табов
        static::endTabContent();

        return ob_get_clean();
    }

    /**
     * Просматривает элементы и конвертирует короткий формат:
     * tab_id => label в формат Nav
     */
    protected function adjustItems(): void
    {
        if (empty($this->items)) {
            return;
        }

        /** @var bool имеется активный элемент */
        $hasActive = false;

        // просматриваем все элементы
        foreach ($this->items as $i => &$item) {
            // если ключ и значение заданы как target => label, то конвертируем в формат Nav
            if (is_string($i) && is_string($item)) {
                $item = [
                    'label' => $item,
                    'url' => 'javascript:',
                    'linkOptions' => [
                        'data' => [
                            'toggle' => 'tab',
                            'bs-toggle' => 'tab',
                            'target' => '#' . $i,
                            'bs-target' => '#' . $i
                        ]
                    ]
                ];
            }

            // проверяем активность
            if (! empty($item['active'])) {
                $hasActive = true;
            }

            // исправление BS4
            if (! empty($item['items'])) {
                $item['linkOptions']['data-bs-toggle'] = 'dropdown';
            }
        }

        unset($item);

        // если не было активных элементов, то устанавливаем активным первый
        if (! $hasActive) {
            $keys = array_keys($this->items);
            $this->items[$keys[0]]['active'] = true;
        }
    }

    /**
     * Начало tab-content
     */
    public static function beginTabContent(array $options = []): void
    {
        Html::addCssClass($options, 'tab-content');
        ob_start();
        echo Html::beginTag('div', $options);
    }

    /**
     * Закрывающий тег tab-content
     */
    public static function endTabContent(): void
    {
        echo ob_get_clean() . Html::endTag('div');
    }

    /**
     * Открывающий тег tab-pane
     */
    public static function beginTab(string $id, bool $active = false, array $options = []): void
    {
        $options['id'] = $id;
        Html::addCssClass($options, 'tab-pane');
        if ($active) {
            Html::addCssClass($options, 'active');
        }

        ob_start();
        echo Html::beginTag('div', $options);
    }

    /**
     * Закрывающий тег tab-pane
     */
    public static function endTab(): void
    {
        echo ob_get_clean() . Html::endTag('div');
    }
}
