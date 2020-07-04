<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.07.20 13:41:21
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\ArrayHelper;
use dicr\helper\Html;
use dicr\widgets\Widget;
use Exception;
use Yii;
use yii\bootstrap4\Nav;

/**
 * Навигационная панель.
 *
 * @noinspection PhpUnused
 */
class NavBar extends Widget
{
    /** @var array опции навигации \yii\bootstrap4\Nav */
    public $nav = [];

    /** @var string дополнительный HTML-контент */
    public $content;

    /** @var array опции панели \app\modules\admin\widgets\ControlPanel */
    public $controlPanel = [];

    /**
     * @var array the HTML attributes for the container tag. The following special options are recognized:
     *
     * - tag: string, defaults to "div", the name of the container tag.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $collapseOptions = [];

    /**
     * @var string|bool the text of the brand or false if it's not used. Note that this is not HTML-encoded.
     * @see https://getbootstrap.com/docs/4.2/components/navbar/
     */
    public $brandLabel = false;

    /**
     * @var string|bool src of the brand image or false if it's not used. Note that this param will override
     *     `$this->brandLabel` param.
     * @see https://getbootstrap.com/docs/4.2/components/navbar/
     * @since 2.0.8
     */
    public $brandImage = false;

    /**
     * @var array|string|bool $url the URL for the brand's hyperlink tag. This parameter will be processed by
     *     [[\yii\helpers\Url::to()]] and will be used for the "href" attribute of the brand link. Default value is
     *     false that means
     * [[\yii\web\Application::homeUrl]] will be used.
     * You may set it to `null` if you want to have no link at all.
     */
    public $brandUrl = false;

    /**
     * @var array the HTML attributes of the brand link.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $brandOptions = [];

    /**
     * @var string the toggle button content. Defaults to bootstrap 4 default `<span
     *     class="navbar-toggler-icon"></span>`
     */
    public $togglerContent = '<span class="navbar-toggler-icon"></span>';

    /**
     * @var array the HTML attributes of the navbar toggler button.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $togglerOptions = [];

    /**
     * @var bool whether the navbar content should be included in an inner div container which by default
     * adds left and right padding. Set this to false for a 100% width navbar.
     */
    public $renderInnerContainer = true;

    /**
     * @var array the HTML attributes of the inner container.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $innerContainerOptions = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if (! isset($this->options['class']) || empty($this->options['class'])) {
            Html::addCssClass($this->options, ['navbar-expand-md', 'navbar-light', 'bg-light']);
        }

        Html::addCssClass($this->options, ['widget' => 'navbar', 'dicr-admin-navbar']);

        if (! isset($this->innerContainerOptions['class'])) {
            Html::addCssClass($this->innerContainerOptions, 'container');
        }

        Html::addCssClass($this->togglerOptions, ['widget' => 'navbar-toggler']);

        if ($this->brandImage !== false) {
            $this->brandLabel = Html::img($this->brandImage);
        }

        if (! isset($this->collapseOptions['id'])) {
            $this->collapseOptions['id'] = "{$this->options['id']}-collapse";
        }

        Html::addCssClass($this->collapseOptions, [
            'collapse' => 'collapse',
            'widget' => 'navbar-collapse'
        ]);

        if ($this->nav !== false) {
            $this->nav['options'] = $this->nav['options'] ?? [];
            Html::addCssClass($this->nav['options'], 'navbar-nav');
        }

        ob_start();
    }

    /**
     * Рендерит бренд.
     *
     * @return string
     */
    protected function renderBrand()
    {
        $brand = '';

        if ($this->brandLabel !== false) {
            Html::addCssClass($this->brandOptions, ['widget' => 'navbar-brand']);
            if ($this->brandUrl === null) {
                $brand = Html::tag('span', $this->brandLabel, $this->brandOptions);
            } else {
                $brand = Html::a($this->brandLabel, $this->brandUrl === false ? Yii::$app->homeUrl : $this->brandUrl,
                    $this->brandOptions);
            }
        }

        return $brand;
    }

    /**
     * Renders collapsible toggle button.
     *
     * @return string the rendering toggle button.
     */
    protected function renderToggleButton()
    {
        return Html::button($this->togglerContent, ArrayHelper::merge($this->togglerOptions, [
            'type' => 'button',
            'data' => [
                'toggle' => 'collapse',
                'target' => '#' . $this->collapseOptions['id'],
            ],
            'aria-controls' => $this->collapseOptions['id'],
            'aria-expanded' => 'false',
        ]));
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @throws Exception
     */
    public function run()
    {
        $content = ob_get_clean();

        NavBarAsset::register($this->view);

        ob_start();

        $navTag = ArrayHelper::remove($this->options, 'tag', 'nav');

        // начало вывода компонента
        echo Html::beginTag($navTag, $this->options);

        // начало container
        if ($this->renderInnerContainer) {
            echo Html::beginTag('div', $this->innerContainerOptions);
        }

        // кнопка раскрытия для мобильных
        echo $this->renderToggleButton();

        // бренд
        echo $this->renderBrand();

        // collapse
        $collapseTag = ArrayHelper::remove($this->collapseOptions, 'tag', 'div');
        echo Html::beginTag($collapseTag, $this->collapseOptions);

        // выводим навигацию
        if (! empty($this->nav['items'])) {
            echo Nav::widget($this->nav);
        }

        // контент между begin/end
        echo $content;

        // закрываем collapse
        echo Html::endTag($collapseTag);

        // дополнительный контент
        if (! empty($this->content)) {
            echo $this->content;
        }

        // выводим control-panel
        if (! empty($this->controlPanel)) {
            echo ControlPanel::widget($this->controlPanel);
        }

        // закрываем container
        if ($this->renderInnerContainer) {
            echo Html::endTag('div');
        }

        // закрываем navbar
        echo Html::endTag($navTag);

        return ob_get_clean();
    }
}
