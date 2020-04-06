<?php

namespace bvb\reporting\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Controller;

/**
 * DeleteController provides an index action for deleting a report file
 */
class DeleteController extends Controller
{
    /**
     * Deletes report file located at $path
     * @param string $path
     * @return mixed
     */
    public function actionIndex($path)
    {
        if(!file_exists($path)){            
            throw new NotFoundHttpException('File not found: '.basename($path));
        }

        unlink($path);
        Yii::$app->session->addFlash('success', 'Report Deleted');
        return $this->redirect(['manage/index']);
    }
}