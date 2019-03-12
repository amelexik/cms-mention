<?php

namespace skeeks\cms\mention\behaviors;

use skeeks\cms\mention\models\Mention;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\validators\SafeValidator;

/**
 * Created by PhpStorm.
 * User: amelexik
 * Date: 22.01.19
 * Time: 15:06
 */
Class CmsMentionBehavior extends Behavior
{
    private $_adminMentions;
    private $_mentions;
    private $_mentionLinks;
    public $mentionSuggest;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT  => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE  => 'afterSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * @param \yii\base\Component $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);
        $owner->validators[] = SafeValidator::createValidator('safe', $owner, ['mentionSuggest']);

    }

    public function getAdminMentions()
    {
        /*
        if ($this->_adminMentions)
            return $this->_adminMentions;
        */
        if ($links = $this->getMentionLinks()) {
            foreach ($links as $cmsContentId => $ids) {
                $ids = array_keys($ids);
                if ($cmsContent = CmsContent::find()->where(['id' => $cmsContentId])->one()) {
                    if ($cmsContent->model_class) {
                        $model = new $cmsContent->model_class;
                        $models = $model::find()->where(['in', $model->idColumn, $ids])->all();
                    } else {
                        $models = CmsContentElement::find()->where(['in', 'id', $ids])->andWhere(['content_id' => $cmsContentId])->all();
                    }

                    if ($models) {
                        foreach ($models as $model) {
                            $this->_adminMentions[$cmsContentId][$model->primaryKey] = $model->name;
                        }
                    }
                }
            }
            return $this->_adminMentions;
        }
    }


    public function getMentions($excl = [])
    {
        if ($this->_mentions)
            return $this->_mentions;
        if ($links = $this->getMentionLinks()) {
            foreach ($links as $cmsContentId => $ids) {
                if(array_key_exists($cmsContentId, $excl))
                    break;
                $ids = array_keys($ids);
                if ($cmsContent = CmsContent::find()->where(['id' => $cmsContentId])->one()) {
                    if ($cmsContent->model_class) {
                        $model = new $cmsContent->model_class;
                        $models = $model::find()
                            ->where(['in', $model->idColumn, $ids])
                            ->published()
                            ->all();
                    } else {
                        $models = CmsContentElement::find()->where(['in', 'id', $ids])->andWhere(['content_id' => $cmsContentId])->all();
                    }

                    if ($models) {
                        foreach ($models as $model) {
                            $this->_mentions[$model->published_at] = $model;
                        }
                    }
                }
            }
            krsort($this->_mentions, SORT_NUMERIC);
            return $this->_mentions;
        }
    }

    public function getMentionLinks()
    {
        if ($this->owner->isNewRecord) return [];

        if ($mentionModel = Mention::find()->setMentionObject($this->owner->content_id, $this->owner->primaryKey)->all()) {
            foreach ($mentionModel as $mention) {
                $cid = intval($mention->cid);
                if (!isset($this->_adminMentions[$cid])) $mentions[$cid] = [];
                $this->_adminMentions[$cid][intval($mention->eid)] = intval($mention->dir);
            }
        }
        return $this->_adminMentions;
    }

    public function getMentionIds()
    {
        $return = [];
        if ($links = $this->getMentionLinks()) {
            foreach ($links as $key => $value) {
                $return[$key] = array_keys($value);
            }
        }
        return $return;
    }


    public function beforeDelete()
    {
        $this->deleteOne($this->owner->content_id, $this->owner->primaryKey);
    }


    public function afterSave()
    {
        $current = $this->getMentionIds();
        $suggest = $this->owner->mentionSuggest;

        $updates = $this->getUpdates($current, $suggest);

        if ($updates['new'] || $updates['delete']) {
            $transaction = \Yii::$app->db->beginTransaction();

            $cid1 = $this->owner->content_id;
            $eid1 = $this->owner->primaryKey;
            if ($updates['new']) {
                foreach ($updates['new'] as $cid2 => $ids) {
                    if ($ids) {
                        foreach ($ids as $eid2)
                            $this->add($cid1, $eid1, $cid2, $eid2);
                    }
                }
            }

            if ($updates['delete']) {
                foreach ($updates['delete'] as $cid2 => $ids) {
                    if ($ids) {
                        foreach ($ids as $eid2)
                            $this->delete($cid1, $eid1, $cid2, $eid2);
                    }
                }
            }

            try {
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                \Yii::error($e->getMessage());
            } catch (\Throwable $e) {
                $transaction->rollBack();
                \Yii::error($e->getMessage());
            }
        }
    }

    /**
     * Возвращает масив из елементов подлежащих insert & delete
     * @param $current
     * @param $suggest
     * @return array
     */
    public function getUpdates($current, $suggest)
    {
        $new = [];
        $delete = [];
        if ($suggest) {
            foreach ($suggest as $key => $values) {
                if (!isset($current[$key])) {
                    $new[$key] = $values;
                    continue;
                }
                foreach ($values as $value) {
                    if (!in_array($value, $current[$key])) {
                        $new[$key][] = $value;
                    }
                }
            }
        }

        if ($current) {
            foreach ($current as $key => $values) {
                if (!isset($suggest[$key])) {
                    $delete[$key] = $values;
                    continue;
                }
                foreach ($values as $value) {
                    if (!in_array($value, $suggest[$key])) {
                        $delete[$key][] = $value;
                    }
                }
            }
        }

        return [
            'new'    => $new,
            'delete' => $delete
        ];
    }

    protected function add($cid1, $eid1, $cid2, $eid2)
    {
        if (!$cid1 || !$eid1 || !$cid2 || !$eid2) return;
        $model = new Mention();
        $model->attributes = [
            'cid1' => $cid1,
            'eid1' => $eid1,
            'cid2' => $cid2,
            'eid2' => $eid2,
        ];
        return $model->save();
    }

    protected function delete($cid1, $eid1, $cid2, $eid2)
    {
        if (!$cid1 || !$eid1 || !$cid2 || !$eid2) return;
        Mention::deleteAll(
            '(cid1 = :cid1 AND eid1 = :eid1 AND cid2 = :cid2 AND eid2 = :eid2) OR (cid1 = :cid2 AND eid1 = :eid2 AND cid2 = :cid1 AND eid2 = :eid1)',
            [':cid1' => $cid1, ':eid1' => $eid1, ':cid2' => $cid2, ':eid2' => $eid2]
        );
    }

    protected function deleteOne($cid1, $eid1)
    {
        if (!$cid1 || !$eid1) return;
        Mention::deleteAll(
            '(cid1 = :cid1 AND eid1 = :eid1) OR (cid2 = :cid1 AND eid2 = :eid1)',
            [':cid1' => $cid1, ':eid1' => $eid1]
        );
    }

}