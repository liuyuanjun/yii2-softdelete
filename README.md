# Yii2 软删 yii2-softdelete
Soft delete trait for Yii2.

> 不影响数据表中唯一键设置，可正常使用Model的 find、delete、save、hasOne、hasMany、via 等方法
> 
> Unique keys will not conflict. You can use the model methods as usual, find、delete、save、hasOne、hasMany、via etc.

> 不保留原硬删方法，直接覆写
> 
> Original query/delete/update method will be overwritten.

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
    
    class Model2 extends \liuyuanjun\yii2\softdelete\ActiveRecord
    {
        public static function getIsDeletedAttribute(): string
        {
            return 'is_deleted';
        }
    }

```

**Then use the model as usual.**