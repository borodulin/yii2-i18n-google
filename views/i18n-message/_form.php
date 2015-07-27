<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\I18nMessage */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="i18n-message-form">

    <?php $form = ActiveForm::begin(['enableClientValidation'=>false, 'options' => ['data-pjax' => true]]) ?>

    <?= $form->field($model, 'category')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'message')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end() ?>

</div>
