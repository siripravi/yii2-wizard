<?php

namespace siripravi\widgets;

use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use app\models\Contact;

class Offcanvas extends Widget
{
    public $offCanvasId = 'wzd-offcanvas';
    public $modalClass = "modal-load";
    public $title;
    public $options;
    /**
     *
     * @var Model
     */
    public $model;
    //public $field;
    /**
     *
     * @var ActiveForm
     */
    protected $form;

    /**
     *
     * @var boolean 
     */

    public $offcontent;
    protected $_contactModel;
    public $titleTag = 'h5';
    public $titleOptions;
    public $size = 'modal-lg';
    public $close = true;
    public $url;
    public $relatedModel = [];

    public $zones;
    public $key;
    public $invId;
    public $center = false;

    public $backdrop = 'false'; // 'true'|'false'|'"static"'

    public $keyboard = 'true';

    public $scroll = 'true';
    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        // $this->model = Invoice::findOne(['id' => $_GET['']]);
        if (empty($this->relatedModel)) {
            $this->relatedModel = [$this->key."_contact_id", Contact::class];
        }
        $this->initContactModel();

        //  if (isset($this->model) && ($this->model->{$field}))
        //    $this->model = Contact::findOne(43);
       // $this->zones = [];
      //  $this->url = Url::to(['/invoice/default/edit-invoice', 'invId' => $this->model->id,'key'=>$this->key,'cId'=>]);
    }

    /**
     * Inits the user model.
     */
    public function initContactModel()
    {
        $field = $this->key."_contact_id";  //$this->relatedModel[0];
        $class = $this->relatedModel[1];
        // print_r($this->model->attributes); 
        // echo $this->model->{$field};
        // die;
        // echo $class; die;
        if ((isset($this->model)) && $this->model->{$field}) {
           // echo $this->model->primaryKey; die;
            $this->_contactModel = $class::findOne($this->model->{$field});
            $this->zones = Zone::find()->select(['zone_id', 'name'])->where(['country_id' => (int)$this->_contactModel->country_id])->all();
        } else {
            $this->_contactModel = new $class();
            //  $this->_contactModel->loadDefaultValues();
        }
    }
    public function run()
    {
        // if ((isset($this->model)) && ($this->model->country_id))
        //     $this->zones = Zone::find()->select(['zone_id', 'name'])->where(['country_id' => (int)$this->model->country_id])->all();

        $this->registerJs();

        $view = $this->getView();
        $js = <<<JS

        function offCanvasLoad(obj, data) {  
            renderData(obj, data.title, '.offcanvas-title');
            renderData(obj, data.body, '.offcanvas-body');
        // renderData(obj, data.footer, '.offCanvas-footer');
        // obj.find('.modal-dialog').removeClass('modal-lg').removeClass('modal-sm').addClass(data.size);
        obj.setAttribute("class", "offcanvas offcanvas-end");   //console.log(obj);
        }

        function renderData(obj, data, sel) {
        
            const elm =  obj.querySelector(sel);
        
            if (data) {       
                elm.innerHTML = data; elm.display = "block";
            } else {
            // elm.display = "none";
            }
            
        }

        function openOffCanvas(action = null, config = {}) {  
        
            if (action === null) {
            /*  var myOffcanvas = document.getElementById('{$this->offCanvasId}');
                
                offCanvasLoad(obj, config);
                if (typeof config.backdrop !== 'undefined') {
                    config.backdrop = {$this->backdrop};
                }
                if (typeof config.keyboard !== 'undefined') {
                    config.keyboard = {$this->keyboard};
                }
                obj.modal({
                    show: true,
                    backdrop: config.backdrop,
                    keyboard: config.keyboard
                });*/
            } else {  // alert(action);  
                $.getJSON(action, function(data){
                    var myOffcanvas = document.getElementById('{$this->offCanvasId}');
                    var bsOffcanvas = new bootstrap.Offcanvas(myOffcanvas);
                    data = $.extend(data, config);
                    offCanvasLoad(myOffcanvas, data);
                    if (!data.backdrop) {
                        data.backdrop = {$this->backdrop};
                    }
                    if (!data.keyboard) {
                        data.keyboard = {$this->keyboard};
                    }
                    if (!data.scroll) {
                        data.scroll = {$this->scroll};
                    }
                /* var bsOffcanvas = new bootstrap.Offcanvas({
                        backdrop: data.backdrop,
                        keyboard: data.keyboard,
                        scroll : data.scroll
                    });*/

                bsOffcanvas.show();
                });
            }
        }
        JS;
        $view->registerJs($js, View::POS_END);

        $js = <<<JS
           
            JS;
        $view->registerJs($js);

        Html::addCssClass($this->titleOptions, 'modal-title');

        Html::addCssClass($this->options, $this->offCanvasId);
        // echo Html::tag('div', $this->content(), ['id' => $this->getId()]);

        //echo "<pre>"; print_r($this->_contactModel->attributes); die;
        return $this->render('offcanvas', [
            'offCanvasId' => $this->offCanvasId,
            'offcontent' => $this->offcontent,
           // 'url' => $this->url,
            'key' => $this->key,
            'model' => $this->_contactModel,
            'zones' => $this->zones,
            'invoice' => $this->model
        ]);
    }

    /**
     * 
     */
    public function registerJs()
    {
        $this->view->registerJs(
            <<<JS
            
                var myOffcanvas = document.getElementById('{$this->offCanvasId}');
                // myOffcanvas.addEventListener('shown.bs.offcanvas', function (e) {
                $(document).on('click', '.btn-buy', function(e){
                    e.stopPropagation();
                    //alert("showing..");
                    var config = {
                    
                        body: $(this).attr('data-modal-body'),
                        footer: $(this).attr('data-modal-footer'),
                    
                    };
                    openOffCanvas("/modal/default/offcanvas", config);
                });

                $(document).on('click', '#{$this->offCanvasId} button[type="submit"]', function(){
                    $('#{$this->offCanvasId} form').trigger('beforeSubmit');
                });
                $(document).on('beforeSubmit', '#{$this->offCanvasId} form', function(){
                    var form = $(this);
                    $.post(form.attr('action'), form.serialize(), function(data){
                        offCanvasLoad($('#{$this->offCanvasId}'), data);
                    }, 'json');
                    return false;
                });
                $(document).on('change','#contact-country_id',function (e) {
                    e.preventDefault();
                    $.post($('#contact-select-zones-wrapper').data('url'),{country_id:$(this).val()},function (json) {
                        $('#contact-select-zones-wrapper select option.gen-op').remove();
                        $('#contact-select-zones-wrapper select')
                            .find('option')
                            .remove()
                            .end();
                        $.each(json.response,function(key,value){
                            $('#contact-select-zones-wrapper select').append($('<option class="gen-op">').text(value.name).attr('value', value.zone_id));
                        });
                      
                    }, 'json');
                });

                JS
        );
    }
}
