<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 00:18:58
 */

declare(strict_types = 1);
namespace dicr\site\admin;

use dicr\helper\ArrayHelper;
use dicr\helper\Html;
use dicr\widgets\Widget;
use Exception;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Nav;

use function ob_get_clean;

/**
 * Навигационная панель.
 */
class NavBar extends Widget
{
    /** @var ?array опции навигации \yii\bootstrap\Nav */
    public ?array $nav = null;

    /** @var ?string дополнительный HTML-контент */
    public ?string $content = null;

    /** @var ?array опции панели \app\modules\admin\widgets\ControlPanel */
    public ?array $controlPanel = null;

    /**
     * @var array the HTML attributes for the container tag. The following special options are recognized:
     *
     * - tag: string, defaults to "div", the name of the container tag.
     *
     * @see \yii\helpers\BaseHtml::renderTagAttributes() for details on how attributes are being rendered.
     */
    public array $collapseOptions = [];

    /**
     * @var ?string the text of the brand or false if it's not used. Note that this is not HTML-encoded.
     * @see https://getbootstrap.com/docs/4.2/components/navbar/
     */
    public ?string $brandLabel = null;

    /**
     * @var ?string src of the brand image or false if it's not used. Note that this param will override
     *     `$this->brandLabel` param.
     * @see https://getbootstrap.com/docs/4.2/components/navbar/
     * @since 2.0.8
     */
    public ?string $brandImage = null;

    /**
     * @var array|string|null $url the URL for the brand's hyperlink tag. This parameter will be processed by
     *     [[\dicr\helper\Url::to()]] and will be used for the "href" attribute of the brand link. Default value is
     *     false that means
     * [[\yii\web\Application::homeUrl]] will be used.
     * You may set it to `null` if you want to have no link at all.
     */
    public string|array|null $brandUrl = null;

    /**
     * @var array the HTML attributes of the brand link.
     * @see \dicr\helper\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public array $brandOptions = [];

    /**
     * @var string the toggle button content. Defaults to bootstrap 4 default `<span
     *     class="navbar-toggler-icon"></span>`
     */
    public string $togglerContent = '<span class="navbar-toggler-icon"></span>';

    /**
     * @var array the HTML attributes of the navbar toggler button.
     * @see \dicr\helper\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public array $togglerOptions = [];

    /**
     * @var bool whether the navbar content should be included in an inner div container which by default
     * adds left and right padding. Set this to false for a 100% width navbar.
     */
    public bool $renderInnerContainer = true;

    /**
     * @var array the HTML attributes of the inner container.
     * @see \dicr\helper\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public array $innerContainerOptions = [];

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (! isset($this->options['class']) || empty($this->options['class'])) {
            Html::addCssClass($this->options, ['navbar-expand-md', 'navbar-light', 'bg-light']);
        }

        Html::addCssClass($this->options, ['widget' => 'navbar', 'dicr-site-admin-navbar']);

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

        if (isset($this->nav)) {
            $options = (array)($this->nav['options'] ?? []);
            Html::addCssClass($options, 'navbar-nav');
            $this->nav['options'] = $options;
        }

        ob_start();
    }

    /**
     * Рендерит бренд.
     */
    protected function renderBrand(): string
    {
        $brand = '';

        if (isset($this->brandLabel)) {
            Html::addCssClass($this->brandOptions, ['widget' => 'navbar-brand']);
            if ($this->brandUrl === null) {
                $brand = Html::tag('span', $this->brandLabel, $this->brandOptions);
            } else {
                $brand = Html::a($this->brandLabel, $this->brandUrl, $this->brandOptions);
            }
        }

        return $brand;
    }

    /**
     * Renders collapsible toggle button.
     */
    protected function renderToggleButton(): string
    {
        return Html::button($this->togglerContent, ArrayHelper::merge($this->togglerOptions, [
            'type' => 'button',
            'data' => [
                'toggle' => 'collapse',
                'bs-toggle' => 'collapse',
                'target' => '#' . $this->collapseOptions['id'],
                'bs-target' => '#' . $this->collapseOptions['id'],
            ],
            'aria-controls' => $this->collapseOptions['id'],
            'aria-expanded' => 'false',
        ]));
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function run(): string
    {
        $content = ob_get_clean();

        AdminAsset::register($this->view);

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
            // fix for BS5
            foreach ((array)$this->nav['items'] as &$item) {
                if (! empty($item['items'])) {
                    $item['linkOptions']['data-bs-toggle'] = 'dropdown';
                }
            }

            unset($item);

            echo Nav::widget($this->nav);
        }

        // контент между begin/end
        echo $content;

        // закрываем collapse
        echo Html::endTag($collapseTag);

        // дополнительный контент
        echo $this->content;

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
