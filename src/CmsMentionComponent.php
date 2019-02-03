<?php

namespace skeeks\cms\mention;

use skeeks\cms\models\CmsContent;
use yii\helpers\ArrayHelper;

class CmsMentionComponent extends \skeeks\cms\base\Component
{
    /**
     * @var array
     */
    public $relatedElementContentIds = [];

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('cms/mention', 'Mention'),
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['relatedElementContentIds'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'relatedElementContentIds' => \Yii::t('cms/mention', 'Search for content items of the following types'),
        ]);
    }

    public function renderConfigForm(\yii\widgets\ActiveForm $form)
    {

        echo $form->fieldSet(\Yii::t('cms/mention', 'Content setting'));
        echo $form->fieldSelectMulti($this, 'relatedElementContentIds', CmsContent::getDataForSelect());
        echo $form->fieldSetEnd();
    }
}