<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\I18nTranslation */

conquer\gii\GiiAsset::register($this);

$this->title = $model->message_id;
$this->params['breadcrumbs'][] = ['label' => 'I18n Translations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="i18n-translation-view">

    <?php if(!\Yii::$app->request->isAjax): ?>
  
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('yii', 'Update') , ['update', 'message_id' => $model->message_id, 'language' => $model->language], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('yii', 'Delete'), ['delete', 'message_id' => $model->message_id, 'language' => $model->language], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?php endif ?>
    
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'message_id',
            'language',
            'translator_id',
            'translation:ntext',
            'status',
            'created_at',
            'updated_at',
            'error_message:ntext',
        ],
    ]) ?>

</div>
