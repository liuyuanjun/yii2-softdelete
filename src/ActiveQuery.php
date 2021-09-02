<?php

namespace liuyuanjun\yii2\softdelete;


/**
 * Class ActiveQuery
 * This is the ActiveQuery class for soft delete.
 *
 * @author  Yuanjun.Liu <6879391@qq.com>
 */
class ActiveQuery extends \yii\db\ActiveQuery
{
    public $withTrashed = false;
    public $onlyTrashed = false;

    /**
     * 包含已经软删的记录
     * @param bool $value
     * @return $this
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function withTrashed(bool $value = true): ActiveQuery
    {
        $this->withTrashed = $value;
        return $this;
    }

    /**
     * 只查找已经软删的记录
     * @param bool $value
     * @return $this
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function onlyTrashed(bool $value = true): ActiveQuery
    {
        $this->onlyTrashed = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function createCommand($db = null)
    {
        if ($this->modelClass && method_exists($this->modelClass, 'getIsDeletedAttribute')) {
            if ($this->onlyTrashed) {
                $this->andWhere(['<>', $this->getTableNameAndAlias()[1] . '.' . $this->modelClass::getIsDeletedAttribute(), 0]);
            } elseif (!$this->withTrashed) {
                $this->andWhere([$this->getTableNameAndAlias()[1] . '.' . $this->modelClass::getIsDeletedAttribute() => 0]);
            }
        }
        return parent::createCommand($db);
    }

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function joinWith($with, $eagerLoading = true, $joinType = 'LEFT JOIN')
    {
        $pQuery = $this;
        $relations = [];
        foreach ((array)$with as $name => $callback) {
            if (is_int($name)) {
                $name = $callback;
                $callback = null;
            }

            if (preg_match('/^(.*?)(?:\s+AS\s+|\s+)(\w+)$/i', $name, $matches)) {
                // relation is defined with an alias, adjust callback to apply alias
                list(, $relation, $alias) = $matches;
                $name = $relation;
                $callback = function ($query) use ($callback, $alias, $pQuery) {
                    /* @var $query ActiveQuery|\yii\db\ActiveQuery */
                    $query->alias($alias);
                    if (empty($pQuery->onlyTrashed) && empty($pQuery->withTrashed) && $query->modelClass && method_exists($query->modelClass, 'getIsDeletedAttribute')) {
                        $query->andOnCondition([$query->getAlias() . '.' . $query->modelClass::getIsDeletedAttribute() => 0]);
                    }
                    if ($callback !== null) {
                        call_user_func($callback, $query);
                    }
                };
            }

            if ($callback === null) {
                $relations[] = $name;
            } else {
                $relations[$name] = $callback;
            }
        }
        $this->joinWith[] = [$relations, $eagerLoading, $joinType];
        return $this;
    }


    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function via($relationName, callable $callable = null)
    {
        $relation = $this->primaryModel->getRelation($relationName);
        if (!$this->onlyTrashed && !$this->withTrashed && $relation->modelClass && method_exists($relation->modelClass, 'getIsDeletedAttribute'))
            $relation->andOnCondition([$relation->getAlias() . '.' . $relation->modelClass::getIsDeletedAttribute() => 0]);
        $callableUsed = $callable !== null;
        $this->via = [$relationName, $relation, $callableUsed];
        if ($callable !== null) {
            call_user_func($callable, $relation);
        }

        return $this;
    }

    /**
     * @return string
     * @date   2021/8/18 17:21
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function getAlias()
    {
        return $this->getTableNameAndAlias()[1];
    }

}
