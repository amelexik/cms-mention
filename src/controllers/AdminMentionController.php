<?php
/**
 * Created by PhpStorm.
 * User: amelexik
 * Date: 09.01.19
 * Time: 21:24
 */

namespace skeeks\cms\mention\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\models\CmsContent;
use yii\db\Query;

Class AdminMentionController extends Controller
{
    public function actionSearch($content_id = null, $q)
    {
        if (!$content_id || !$q)
            return;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if ($cmsContent = CmsContent::find()->where(['id' => $content_id])->one()) {
            if ($cmsContent->model_class) {
                $model = new $cmsContent->model_class;
                $query = $model::find();
                $table = $model->tableName();
                $idColumn = $model->getIdColumn();
                $nameColumn = $model->getNameColumn();
                $query->select("$idColumn as id, $nameColumn AS text")
                    ->from($table)
                    ->where(['like', $nameColumn, $q]);
            } else {
                $query = new Query();
                $query->select('id, name AS text')
                    ->from('{{%cms_content_element}}')
                    ->where(['like', 'name', $q])
                    ->andWhere(['in', 'content_id', $content_id]);
            }

            $query->limit(10);

            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        return $out;
    }
}
