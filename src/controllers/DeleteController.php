<?php

namespace bvb\reporting\controllers;

use Yii;
use yii\web\NotFoundHttpException;

/**
 * DeleteController provides an index action for deleting a report file
 */
class DeleteController extends \yii\web\Controller
{
    /**
     * Only allow site admins to enter
     */
    use \bvb\user\backend\controllers\traits\AdminAccess;

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