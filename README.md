# Yii2 软删 yii2-softdelete
Soft delete trait for Yii2.

> 不保留原硬删方法，直接覆写原方法，个人认为如果数据表使用了软删就没有必要在程序中保留硬删，这可能会导致混乱，如果需要硬删，应该走数据变更的方式，并留下执行记录。
> 
> 避免了很多软删数据表唯一键设置冲突的问题，设置唯一索引需要将 is_deleted 字段加入，作联合唯一
> 
> 可正常使用Model的 find、delete、save、hasOne、hasMany、via 等方法

## 安装 Installation

Pull this package in through Composer.

```js

    {
        "require": {
            "liuyuanjun/yii2-softdelete": "dev-main"
        }
    }

```

or run in terminal:
`composer require liuyuanjun/yii2-softdelete`


## 用法 Usage

### 在Model类中引入  Use the trait in your model.
```php

    use liuyuanjun\yii2\softdelete\SoftDeleteTrait;
        
    /*
    ⚠️ 注意 Attention:
    数据库必须使用自增int作为主键！'is_deleted'字段类型与主键相同且默认值设置为0
    The table must use the auto-increment int as the primary key. The 'is_deleted' column set the same type as the primary key and '0' as the default value.
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

**Then use the model as usual.**