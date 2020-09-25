# yii2-reporting

Traits that can be applied to Yii classes to keep a report of the progress of a task

To configure for the console, add the following to your configuration:
```
    'aliases' => [
        '@bvb-reporting' => '@vendor/brianvb/yii2-reporting/src'
    ],
    'components' => [
        'reporting' => [
            'class' => bvb\reporting\components\ConsoleReporting::class,
            'sendEmailOnlyOnError' => false,
            'recipients' => [
                'example@example.com'
            ]
        ],
    ]
```

And, add the the following application parameter:
```
	[
		'fromEmail' => 'from@from.com'
	]
```

To create a report:
```
Yii::$app->reporting->startReport(['title' => 'Report Title']);
Yii::$app->reporting->addInfo('Information entry');
Yii::$app->reporting->addSummary('Summary entry');
foreach(){
    Yii::$app->reporting->startGroup();
    Yii::$app->reporting->addInfo('Information to');
    Yii::$app->reporting->addInfo('group together');
    Yii::$app->reporting->endGroup();
}
Yii::$app->reporting->addSummary('Final Summary entry');
```