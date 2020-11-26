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
     * Render a view listing details of the report
     * @param string $path 
     * @param boolean $showFullReport
     * @return mixed
     */
    public function actionIndex($path, $showFullReport = true)
    {
        if(!file_exists($path)){            
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->render('index', [
            'report' => ReportHelper::loadFromPath($path),
            'showFullReport' => $showFullReport
        ]);
    }
}