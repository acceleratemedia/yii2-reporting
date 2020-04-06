<?php

/* @var $this yii\web\View */
/* @var $reportsDetails [] */

use bvb\admin\grid\ActionColumn;
use bvb\admin\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

$this->title = 'Manage Reports';

$dataProvider = new ArrayDataProvider([
    'allModels' => $reportOverviews,
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'label' => 'Title',
            'value' => function($data){
                return $data['title'];
            }
        ],
        [
            'label' => 'Warnings',
            'value' => function($data){
                return $data['warningEntries'];
            }
        ],
        [
            'label' => 'Errors',
            'value' => function($data){
                return $data['errorEntries'];
            }
        ],
        [
            'label' => 'Date',
            'value' => function($data){
                return $data['date'];
            }
        ],

        [
            'class' => ActionColumn::class,
            'buttons' => [
                'view' => function($url, $data, $key){
                    return Html::a(
                        '<i class="fas fa-eye"></i>',
                        [
                            'view/index',
                            'path' => $data['path'],
                        ],
                        [
                            'title' => 'View Report'
                        ]
                    );
                },
                'delete' => function($url, $data, $key){
                    return Html::a(
                        '<i class="fas fa-trash"></i>',
                        [
                            'delete/index',
                            'path' => $data['path'],
                        ],
                        [
                            'title' => 'Delete Report',
                            'onclick' => 'return confirm("Are you sure you would like to delete this report?");'
                        ]
                    );
                },
            ],
            'template' => '{view} {delete}'
        ],
    ],
]);
