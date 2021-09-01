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
    private $_softDeleteWhereIsAdded = false;

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function createCommand($db = null)
    {
        $this->addSoftDeleteWhere();
        return parent::createCommand($db);
    }

    /**
     * {@inheritdoc}
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function joinWith($with, $eagerLoading = true, $joinType = 'LEFT JOIN')
    {
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
                $callback = function ($query) use ($callback, $alias) {
                    /* @var $query ActiveQuery|\yii\db\ActiveQuery */
                    $query->alias($alias);
                    if ($query->modelClass && method_exists($query->modelClass, 'getIsDeletedAttribute'))
                        $query->andOnCondition([$query->getAlias() . '.' . $query->modelClass::getIsDeletedAttribute() => 0]);
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
        if ($relation->modelClass && method_exists($relation->modelClass, 'getIsDeletedAttribute'))
            $relation->andOnCondition([$relation->getAlias() . '.' . $relation->modelClass::getIsDeletedAttribute() => 0]);
        $callableUsed = $callable !== null;
        $this->via = [$relationName, $relation, $callableUsed];
        if ($callable !== null) {
            call_user_func($callable, $relation);
        }

        return $this;
    }

    /**
     * 添加软删 where
     * @date   2021/8/18 17:51
     * @author Yuanjun.Liu <6879391@qq.com>
     */
    public function addSoftDeleteWhere()
    {
        if (!$this->_softDeleteWhereIsAdded && $this->modelClass && method_exists($this->modelClass, 'getIsDeletedAttribute')) {
            $this->andWhere([$this->getTableNameAndAlias()[1] . '.' . $this->modelClass::getIsDeletedAttribute() => 0]);
            $this->_softDeleteWhereIsAdded = true;
        }
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
