<?php

use liuyuanjun\yii2\softdelete\SoftDeleteActiveQuery;
use yii\base\Event;
use yii\db\Exception;

// 检查find是否被错误覆写
Event::on(\yii\db\ActiveQuery::class, \yii\db\ActiveQuery::EVENT_INIT, function (Event $event) {
    if (!($event->sender instanceof SoftDeleteActiveQuery) && method_exists($event->sender->modelClass, 'getIsDeletedAttribute')) {
        throw new Exception($event->sender->modelClass . '::find 不支持软删，请更改为 ' . SoftDeleteActiveQuery::class . ' 或其子类');
    }
});