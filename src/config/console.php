<?php
return [
    'components' => [
        'mention' => [
            'class' => 'skeeks\cms\mention\CmsMentionComponent',
        ],
        'i18n' => [
            'translations' => [
                'cms/mention' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@skeeks/cms/mention/messages',
                ]
            ]
        ]
    ],
    'modules'    =>
        [
            'mention' => [
                'class' => 'skeeks\cms\mention\CmsMentionModule',

            ]
        ]
];