<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

conquer\gii\GiiAsset::register($this);

$this->title = 'I18n Messages';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="i18n-message-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
    <?= Html::button('Create I18n Message', [
        'class' => 'btn btn-success show-modal',
        'value' => Url::to(['create']),
        'data-target' => '#modal_view',
        'data-header' => 'Create I18n Message',
    ]); ?>
    </p>

    <?= Modal::widget([
        'id' => 'modal_view',
    ]); ?>

    <?php Pjax::begin(['id'=>'pjax-i18n-message-index']);?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'message_id',
            'category',
            'message:ntext',
            'created_at',
            'updated_at',

            [
                'class' => 'yii\grid\ActionColumn',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $options = array_merge([
                            'title' => Yii::t('yii', 'View'),
                            'aria-label' => Yii::t('yii', 'View'),
                            'data-pjax' => '0',
                            'class'=>'show-modal',
                            'value' => $url,
                            'data-target' => '#modal_view', 
                            'data-header' => Yii::t('yii', 'View') . ' ' . 'I18n Messages',
                        ]);
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', 'javascript:;', $options);
                    },
                    'update' => function ($url, $model, $key) {
                        $options = array_merge([
                            'title' => Yii::t('yii', 'Update'),
                            'aria-label' => Yii::t('yii', 'Update'),
                            'data-pjax' => '0',
                            'class'=>'show-modal',
                            'value' => $url, 
                            'data-target' => '#modal_view', 
                            'data-header' => Yii::t('yii', 'Update') . ' ' . 'I18n Messages',
                        ]);
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:;', $options);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end();?>
    
</div>
