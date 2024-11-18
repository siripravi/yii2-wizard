<?php

namespace siripravi\wizard\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for Wizard Widget
 *
 * @author A.F.Schuurman <andre.schuurman+yii2-wizardwidget@gmail.com>
 * @since 1.0
 */
class WizardWidgetAsset extends AssetBundle
{

    
	public $depends = [
		'yii\web\YiiAsset',
		'yii\bootstrap5\BootstrapPluginAsset'
	];
	public $css = [
		'css/wizardwidget.css',
	];
	public $js = [
		//'js/wizardwidget.js'
	];
	public function registerAssetFiles($view)
    {
      //  $this->css[] = $this->style . '/_all.css';
        parent::registerAssetFiles($view);
    }
	public function init()
    {
        $this->sourcePath = __DIR__;
        parent::init();
    }
}