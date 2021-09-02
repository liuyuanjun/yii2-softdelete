<?php

namespace liuyuanjun\yii2\softdelete;

use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\StaleObjectException;

/**
 * Trait SoftDelete 软删
 *
 * - 必须有 INT 类 AUTO_INCREMENT 作为主键
 * - 必须有删除标记字段类型与主键相同并默认值设置0 默认字段名「is_deleted」，字段名不一样需覆写 getIsDeletedAttribute() 方法
 * - 数据表中如果需要建唯一索引，需加入 「is_deleted」做联合唯一
 * - 没有另写方法，无需改变原有使用习惯
 * - 正常使用原有的 find、delete、save、hasOne、hasMany、via、with、joinWith 等方法。除了直接join联表无法支持其它基本都没问题
 * - 数据库建好字段，在model中use即可
 *
 * @author  Yuanjun.Liu <6879391@qq.com>
 */
trait SoftDeleteTrait
{

    /**
     * 标记已删除字段名
     * @return string
     * @date   2020/11/25 20:46
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public static function getIsDeletedAttribute(): string
    {
        return 'is_deleted';
    }

    /**
     * 删除时间字段名
     * @return string
     * @date   2020/11/25 20:45
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public static function getDeleteTimeAttribute(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public static function find(): ActiveQuery
    {
        return new ActiveQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function update($runValidation = true, $attributeNames = null)
    {
        if (($isDeletedAttr = static::getIsDeletedAttribute()) && $this->$isDeletedAttr <> 0) {
//            Yii::info('Model已被软删，无法更新.', __METHOD__);
            return false;
        }
        return parent::update($runValidation, $attributeNames);
    }

    /**
     * force delete
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function forceDelete()
    {
        return $this->delete(true);
    }

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function delete($force = false)
    {
        if (!$this->isTransactional(self::OP_DELETE)) {
            return $this->deleteInternal($force);
        }
        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->deleteInternal($force);
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    protected function deleteInternal($force = false)
    {
        if (!$this->beforeDelete()) {
            return false;
        }

        // we do not check the return value of deleteAll() because it's possible
        // the record is already deleted in the database and thus the method will return 0
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock !== null) {
            $condition[$lock] = $this->$lock;
        }
        $result = $force ? parent::deleteAll($condition) : static::deleteAll($condition);
        if ($lock !== null && !$result) {
            throw new StaleObjectException('The object being deleted is outdated.');
        }
        $this->setOldAttributes(null);
        $this->afterDelete();

        return $result;
    }

    /**
     * force delete all
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public static function forceDeleteAll($condition = null, $params = [])
    {
        return parent::deleteAll($condition, $params);
    }

    /**
     * force update all
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public static function forceUpdateAll($attributes, $condition = '', $params = [])
    {
        return parent::updateAll($attributes, $condition, $params);
    }

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public static function deleteAll($condition = null, $params = [])
    {
        $isDeletedAttr = static::getIsDeletedAttribute();
        $condition = $condition ? ['and', [$isDeletedAttr => 0], $condition] : [$isDeletedAttr => 0];
        $command = static::getDb()->createCommand();
        $data = [$isDeletedAttr => new Expression(static::primaryKey()[0])];
        if ($deleteTimeAttr = static::getDeleteTimeAttribute()) {
            $data[$deleteTimeAttr] = date('Y-m-d H:i:s');
        }
        $command->update(static::tableName(), $data, $condition, $params);
        return $command->execute();
    }

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public static function updateAll($attributes, $condition = '', $params = []): int
    {
        $isDeletedAttr = static::getIsDeletedAttribute();
        $condition = $condition ? ['and', [$isDeletedAttr => 0], $condition] : [$isDeletedAttr => 0];
        return parent::updateAll($attributes, $condition, $params);
    }

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public static function updateAllCounters($counters, $condition = '', $params = []): int
    {
        $isDeletedAttr = static::getIsDeletedAttribute();
        $condition = $condition ? ['and', [$isDeletedAttr => 0], $condition] : [$isDeletedAttr => 0];
        return parent::updateAllCounters($counters, $condition, $params);
    }

}
