# Yii2 软删 yii2-softdelete

Soft delete trait for Yii2.

- 替换了 yii\db\ActiveRecord 的 delete() 方法，软删除时将 is_deleted 字段设置为主键字段的值，而不是删除数据，从而避免了使用删除时间等来标记软删时唯一索引可能产生冲突的问题(设置唯一索引需要将 is_deleted 字段加入，作联合唯一索引)
- 使用forceDelete()方法可硬删除数据
- withTrashed() 方法可在查询时包含软删数据  onlyTrashed() 方法可在查询时只包含软删数据
- 可正常使用Model的 find、delete、save、hasOne、hasMany、via 等方法

## 安装

`composer require liuyuanjun/yii2-softdelete`


## 用法 Usage

### 在Model类中引入

```php

    use liuyuanjun\yii2\softdelete\SoftDeleteTrait;
        
    /*
    ⚠️ 注意 Attention:
    数据表主键类型只能为 int 或者 string ！'is_deleted'字段类型与主键相同且默认值设置为 0 或 空字符串。
    */
    class Model extends \Yii\db\ActiveRecord
    {
        use SoftDeleteTrait;
        
        /**
         * is_deleted column name 
         * The default value is 'is_deleted', if you want to change it overwrite this method.
         * @return string
         * @author Yuanjun.Liu <6879391@qq.com>
        */
        public static function getIsDeletedAttribute(): string
        {
            return 'is_deleted';
        }
    }
    
    //+++++ OR +++++
    
    class Model2 extends \liuyuanjun\yii2\softdelete\SoftDeleteActiveRecord
    {
        public static function getIsDeletedAttribute(): string
        {
            return 'is_deleted';
        }
    }

```

** 然后像往常一样正常使用 **
