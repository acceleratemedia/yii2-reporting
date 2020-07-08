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