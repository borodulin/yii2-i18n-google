<?php

namespace app\controllers;

use Yii;
use conquer\i18n\models\I18nTranslation;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * I18nTranslationController implements the CRUD actions for I18nTranslation model.
 */
class I18nTranslationController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all I18nTranslation models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => I18nTranslation::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single I18nTranslation model.
     * @param integer $message_id
     * @param string $language
     * @return mixed
     */
    public function actionView($message_id, $language)
    {
        if(Yii::$app->request->isAjax)
            return $this->renderAjax('view', [
                'model' => $this->findModel($message_id, $language),
            ]);
        else
            return $this->render('view', [
                'model' => $this->findModel($message_id, $language),
            ]);
    }

    /**
     * Creates a new I18nTranslation model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new I18nTranslation();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if(Yii::$app->request->isAjax)
                return $this->redirect(['index']);
            else
                return $this->redirect(['view', 'message_id' => $model->message_id, 'language' => $model->language]);
        } else {
            if(Yii::$app->request->isAjax)
                return $this->renderAjax('create', [
                    'model' => $model,
                ]);
            else
                return $this->render('create', [
                    'model' => $model,
                ]);
        }
    }

    /**
     * Updates an existing I18nTranslation model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $message_id
     * @param string $language
     * @return mixed
     */
    public function actionUpdate($message_id, $language)
    {
        $model = $this->findModel($message_id, $language);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if(Yii::$app->request->isAjax)
                return $this->redirect(['index']);
            else
                return $this->redirect(['view', 'message_id' => $model->message_id, 'language' => $model->language]);
        } else {
            if(Yii::$app->request->isAjax)
                return $this->renderAjax('update', [
                    'model' => $model,
                ]);
            else
                return $this->render('update', [
                    'model' => $model,
                ]);
        }
    }

    /**
     * Deletes an existing I18nTranslation model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $message_id
     * @param string $language
     * @return mixed
     */
    public function actionDelete($message_id, $language)
    {
        $this->findModel($message_id, $language)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the I18nTranslation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $message_id
     * @param string $language
     * @return I18nTranslation the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($message_id, $language)
    {
        if (($model = I18nTranslation::findOne(['message_id' => $message_id, 'language' => $language])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
