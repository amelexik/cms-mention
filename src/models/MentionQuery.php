<?php

namespace skeeks\cms\mention\models;

/**
 * This is the ActiveQuery class for [[Mention]].
 *
 * @see Mention
 */
class MentionQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Mention[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Mention|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function setMentionObject($cms_content_id=null,$cms_content_element_id=null){
        if (is_null($cms_content_id) || is_null($cms_content_element_id)) return $this;
        $this->addSelect(
            [
                '*',
                'cid' => new \yii\db\Expression('CASE WHEN cid1=' . $cms_content_id . ' AND eid1=' . $cms_content_element_id . ' THEN cid2 ELSE cid1 END'),
                'eid' => new \yii\db\Expression('CASE WHEN cid1='.$cms_content_id.' AND eid1='.$cms_content_element_id.' THEN eid2 ELSE eid1 END'),
                'dir' => new \yii\db\Expression('CASE WHEN cid1='.$cms_content_id.' AND eid1='.$cms_content_element_id.' THEN 0 ELSE 1 END'),
            ]
        );

        $this->where('(cid1='.$cms_content_id.' AND eid1='.$cms_content_element_id.') OR (cid2='.$cms_content_id.' AND eid2='.$cms_content_element_id.')');
        return $this;
    }
}
