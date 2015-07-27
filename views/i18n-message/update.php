<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\I18nMessage */

conquer\gii\GiiAsset::register($this);

$this->title = Yii::t('yii', 'Update') . ' ' . 'I18n Message' . ' ' . $model->message_id;
$this->params['breadcrumbs'][] = ['label' => 'I18n Messages', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->message_id, 'url' => ['view', 'id' => $model->message_id]];
$this->params['breadcrumbs'][] = Yii::t('yii', 'Update');
?>

<?php Pjax::begin(['id'=>'pjax-i18n-message-update']) ?>

<div class="i18n-message-update">

    <?php if(!\Yii::$app->request->isAjax): ?>
    
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php endif ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php Pjax::end() ?>