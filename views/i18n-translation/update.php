<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\I18nTranslation */

conquer\gii\GiiAsset::register($this);

$this->title = Yii::t('yii', 'Update') . ' ' . 'I18n Translation' . ' ' . $model->message_id;
$this->params['breadcrumbs'][] = ['label' => 'I18n Translations', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->message_id, 'url' => ['view', 'message_id' => $model->message_id, 'language' => $model->language]];
$this->params['breadcrumbs'][] = Yii::t('yii', 'Update');
?>

<?php Pjax::begin(['id'=>'pjax-i18n-translation-update']) ?>

<div class="i18n-translation-update">

    <?php if(!\Yii::$app->request->isAjax): ?>
    
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php endif ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php Pjax::end() ?>