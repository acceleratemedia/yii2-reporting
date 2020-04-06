<?php

namespace bvb\reporting\helpers;

use bvb\reporting\models\Report;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;

/**
 * ReportHelper contains constants and helper functions pertaining to Reports
 */
class ReportHelper
{
	/**
	 * Creates a json object from the report with the some of its properties
	 * @param $report \bvb\reporting\models\Report
	 * @return string
	 */
	static function toJson($report)
	{
		$reportData = [
			'title' => $report->title,
			'entryLevels' => $report->getEntryLevels(),
			'entries' => [],
			// 'summaryEntries' => $report->getSummaryEntries(),
			'timestamp' => $report->getTimestamp()
		];
		foreach($report->getEntries() as $entry){
			$reportData['entries'][] = [
                'level' => $entry->level,
                'message' => $entry->message,
                'category' => $entry->category,
                'groupId' => $entry->groupId,
                'timestamp' => $entry->timestamp,
			];
		}
		return json_encode($reportData);
	}

	/**
	 * Returns
	 * @param string $path 
	 * @return \bvb\reporting\models\Report
	 */
	static function loadFromPath($path)
	{
		return (new Report())->loadFromPath($path);
	}

	/**
	 * Return an array of paths to saved report files
	 * @param string $path
	 * @return array
	 */
	static function searchPath($path)
	{
		$path = Yii::getAlias($path);
		$reportFilePaths = [];
		if(file_exists($path)){
			$it = new RecursiveDirectoryIterator($path);
			foreach(new RecursiveIteratorIterator($it) as $file){
				if(!$file->isDir()){
					$reportFilePaths[] = $file->getPathName();
				}
			}			
		}
		return $reportFilePaths;
	}

	/**
	 * Returns basic details from a report by opening it and getting the
	 * title as well as other summarized items
	 * @return array
	 */
	static function getOverview($path)
	{
		$reportData = json_decode(file_get_contents($path), true);
		return [
			'path' => $path,
			'title' => $reportData['title'],
			'warningEntries' => $reportData['entryLevels']['warning'],
			'errorEntries' => $reportData['entryLevels']['error'],
			'date' => basename($path, '.json')
		];
	}

	/**
	 * Sends the report contents in an email
	 * @param \bvb\reporting\models\Report $report
	 * @param array $recipients

	 * @return boolean
	 */
	static function email($report, $recipients)
	{
		$mailerDefaultHtmlLayout = Yii::$app->mailer->htmlLayout;
        Yii::$app->mailer->htmlLayout = '@bvb-reporting/mail/layouts/html';
        $return = Yii::$app->mailer->compose('@bvb-reporting/views/view/index', [
                'report' => $report
            ])
            ->setFrom(Yii::$app->params['fromEmail'])
            ->setTo($recipients)
            ->setSubject(Yii::$app->name.': '.$report->title)
            ->send();
        Yii::$app->mailer->htmlLayout = $mailerDefaultHtmlLayout;
        return $return;
	}


	/**
	 * Returns entries in the report for the given level
	 * @param \bvb\reporting\models\Report $report
	 * @param string $level
	 * @return array
	 */
	static function getEntriesByLevel($report, $level)
	{
		$entries = [];
		foreach($report->getEntries() as $entry){
			if($entry->level == $level){
				$entries[] = $entry;
			}
		}
		return $entries;
	}
}