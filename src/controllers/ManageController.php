<?php

namespace bvb\reporting\controllers;

use bvb\reporting\helpers\ReportHelper;

/**
 * ManageController provides an index action for viewing lists of reports
 */
class ManageController extends \yii\web\Controller
{
    /**
     * Only allow site admins to enter
     */
    use \bvb\user\backend\controllers\traits\AdminAccess;

    /**
     * Render a view listing out available reports to be viewed
     * @return mixed
     */
    public function actionIndex()
    {
        $reportFilePaths = [];
        foreach($this->module->reportPaths as $path){
            $reportFilePaths = array_merge($reportFilePaths, ReportHelper::searchPath($path));
        }

        $reportOverviews = [];
        foreach($reportFilePaths as $reportFilePath){
            $reportOverviews[] = ReportHelper::getOverview($reportFilePath, $reportFilePath);
        }

        return $this->render('index', [
            'reportOverviews' => $reportOverviews
        ]);
    }
}