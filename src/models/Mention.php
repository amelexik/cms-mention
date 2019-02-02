<?php

namespace skeeks\cms\mention\models;

use skeeks\cms\models\CmsContent;
use Yii;

/**
 * This is the model class for table "mention".
 *
 * @property int $eid1 Element 1 primary key
 * @property int $cid1 Content Type 1 primary key
 * @property int $eid2 Element 2 primary key
 * @property int $cid2 Content Type 2  primary key
 *
 * @property CmsContent $cid10
 * @property CmsContent $cid20
 */
class Mention extends \yii\db\ActiveRecord
{
    /**
     * @var
     */
    private $_cmsContentId;
    /**
     * @var
     */
    private $_cmsContentElementId;


    /**
     * @var
     * cid - CmsContent.id
     */
    public $cid;
    /**
     * @var
     * eid - CmsContentElement.id
     */
    public $eid;
    /**
     * @var
     * dir - direction
     */
    public $dir;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mention';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['eid1', 'cid1', 'eid2', 'cid2'], 'required'],
            [['eid1', 'cid1', 'eid2', 'cid2'], 'integer'],
            [['cid1'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContent::className(), 'targetAttribute' => ['cid1' => 'id']],
            [['cid2'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContent::className(), 'targetAttribute' => ['cid2' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'eid1' => Yii::t('cms/mention', 'Element 1 primary key'),
            'cid1' => Yii::t('cms/mention', 'Content Type 1 primary key'),
            'eid2' => Yii::t('cms/mention', 'Element 2 primary key'),
            'cid2' => Yii::t('cms/mention', 'Content Type 2  primary key'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCid10()
    {
        return $this->hasOne(CmsContent::className(), ['id' => 'cid1']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCid20()
    {
        return $this->hasOne(CmsContent::className(), ['id' => 'cid2']);
    }

    /**
     * {@inheritdoc}
     * @return MentionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MentionQuery(get_called_class());
    }


    /**
     * @param $value
     */
    public function setCmsContentId($value)
    {
        $this->_cmsContentId = $value;
    }

    /**
     * @return mixed
     */
    public function getCmsContentId()
    {
        return $this->_cmsContentId;
    }

    /**
     * @param $value
     */
    public function setCmsContentElementId($value)
    {
        $this->_cmsContentElementId = $value;
    }

    /**
     * @return mixed
     */
    public function getCmsContentElementId()
    {
        return $this->_cmsContentElementId;
    }
}
