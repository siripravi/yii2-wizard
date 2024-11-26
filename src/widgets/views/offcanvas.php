<?php

/**
 * Created by PhpStorm.
 * User: Purnachandra Rao
 * Date: 19.12.23
 * Time: 15:05
 *
 * @var $options array
 * @var $titleTag string
 * @var $titleOptions string
 * @var $size string
 * @var $close boolean
 * @var $center boolean
 */

use yii\helpers\Html;
use yii\bootstrap5\Offcanvas;
use kartik\markdown\MarkdownEditor;
use modules\invoice\models\Invoice;

use kartik\widgets\Select2;


use yii\helpers\Url;

use yii\web\JsExpression;
use yii\bootstrap5\ActiveForm;
use app\widgets\Check;
use yii\helpers\ArrayHelper;
use app\models\Country;
use demogorgorn\ajax\AjaxSubmitButton;

?>

<?php
//$invoice = new Contact(); //Invoice();
//$key = "billto_contact_id";
?>
<!--= $this->render('_invoiceFor', ['invoice' => $invoice, 'zones' => $zones, 'contact' => $contact, 'url' => ['/contact/update', 'id' => $contact->id]]); -->
<?php Offcanvas::begin([
    'id' => $offCanvasId,
    // 'placement' => Offcanvas::PLACEMENT_END,
    //'size' => Offcanvas::FULL_SCREEN,
    'title' => $this->title,
    'backdrop' => false,
    // 'model' => $model,
    //'field' => "billto_contact_id",

    'scrolling' => true,
    'bodyOptions' => ['class' => 'w-100']
]); ?>
<!-- Offcanvas -->
<?php
echo '<label class="control-label">Select Contact</label>';
$template = '<address><p class="repo-language">{{adrs}}</p></adrs>';
echo Select2::widget([
    'name' => $key,
    'options' => ['placeholder' => 'Filter as you type ...'],
    'size' => Select2::SMALL,
    'pluginOptions' => [
        'minimumInputLength' => 3,
        // 'dropdownParent' => '#modal',       
        'ajax' => [
            'url' => Url::to(['/contact/select2']),
            'dataType' => 'json',
            'data' => new JsExpression('function(params) { return {q:params.term}; }')
        ],
        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
        'templateResult' => new JsExpression('function(data) { return data.text; }'),
        'templateSelection' => new JsExpression('function (data) { return data.text; }'),
    ],
    'pluginEvents' => [
        'change' => new JsExpression('function() {
                        var selectedId = $(this).val();
                        var key = "' . $key . '";               
                        var invId = ' . $invoice->id . ';      
                        var url = "/modal/default/edit-invoice?invId="+invId+"&key="+key+"&cId="+selectedId;
                        // $.pjax.reload({container: "#testing", data:{tags:selectedIds}});
                        console.log(url);
                        $.ajax({
                            "type":"POST",
                            "beforeSend":function(html){  
                                 // alert("sending...");                        
                                  //  return false;
                            },
                            "success":function(html){
                                $("#output").html(html.message);
                                // $.pjax.reload({container:"#pjax_customer"});
                            },
                            "url":url
                        });
                        return false;
                    }')
    ],
    'addon' =>
    [
        'prepend' => [
            'content' => '<i class="fas fa-globe"></i>'
        ],
        'append' => [
            'content' => Html::button('<i class="fas fa-map-marker-alt"></i>', [
                'class' => 'btn btn-primary',
                'title' => 'Mark on map',
                'data-toggle' => 'tooltip'
            ]),
            'asButton' => true
        ]
    ]
]); ?>
<?php yii\widgets\Pjax::begin(['id' => 'pjax_contact_form']) ?>
<p id="output"></p>
<?php $form = ActiveForm::begin([

    'enableAjaxValidation' => false,
    // 'action' => $url,
    'options' => ['data-pjax' => true]
]) ?>
<div class="contact-form">
<div class="row">
        <div class="col-lg-12">
            <?= $form->field($invoice,$key."_address")->textArea(['rows' => 4]) ?>
        </div>      
    </div>
</div>
    <?php AjaxSubmitButton::begin([
        'label' => 'Save Contact',
        'ajaxOptions' => [
            'type' => 'POST',
            'url' => Url::to(['/modal/default/edit-invoice', 'invId' => $invoice->id, 'key' => $key, 'cId' => $model->id]),
            'beforeSend' => new \yii\web\JsExpression('function(html){
           
            }'),
            //  'dataType'=>'jsonp',
            'success' => new \yii\web\JsExpression('function(html){
            $("#output").html(html.message);
           //$.pjax.reload({container:"#pjax_customer"});
            }'),
        ],
        'options' => ['class' => 'btn btn-success', 'type' => 'submit'],
    ]);
    AjaxSubmitButton::end();
    ?>
    <?php ActiveForm::end(); ?>
    <?php yii\widgets\Pjax::end() ?>
    <?php Offcanvas::end();  ?>