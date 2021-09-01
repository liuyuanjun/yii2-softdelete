<?php

namespace liuyuanjun\yii2\softdelete;

/**
 * 软删 Active Record
 * ActiveRecord with soft delete
 *
 * @author  Yuanjun.Liu <6879391@qq.com>
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
    use SoftDeleteTrait;
}
