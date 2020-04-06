<?php

namespace bvb\reporting\controllers;

use bvb\reporting\helpers\ReportHelper;
use Yii;
use yii\base\ErrorException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ViewController provides an index action for viewing an individual report
 */
class ViewController extends Controller
{
    /**
     * Render a view listing out available reports to be viewed
     * @return mixed
     */
    public function actionIndex($path)
    {
        if(!file_exists($path)){            
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->render('index', [
            'report' => ReportHelper::loadFromPath($path)
        ]);
    }
}