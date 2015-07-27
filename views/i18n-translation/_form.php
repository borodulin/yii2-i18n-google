<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\I18nTranslation */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="i18n-translation-form">

    <?php $form = ActiveForm::begin(['enableClientValidation'=>false, 'options' => ['data-pjax' => true]]) ?>

    <?= $form->field($model, 'message_id')->textInput() ?>

    <?= $form->field($model, 'language')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'translator_id')->textInput() ?>

    <?= $form->field($model, 'translation')->textarea(['rows' => 6]) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end() ?>

</div>
