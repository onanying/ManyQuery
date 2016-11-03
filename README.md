## ManyQuery

想做一个淘宝/京东/电影网站一样的很多类型的分类筛选？还得支持搜索？ 看看我这个吧，通用的多条件查询类。

![image](https://raw.githubusercontent.com/onanying/ManyQuery/master/screenshot.png)

### 配置

```PHP
// 配置
$query = new ManyQuery();
$aConf = [
    [
        'name' => 'search',
        'type' => 'search',
        'label' => '搜索: ?',
        'where' => "users.account = '?' OR users.id_card = '?' OR users.name LIKE '%?%'",
    ],
    [
        'name' => 'sex',
        'type' => 'select',
        'label' => '性别: ?',
        'option' => [
            '1' => '男',
            '0' => '女',
        ],
        'where' => [
            '1' => "users.sex = 1",
            '0' => "users.sex = 0",
        ],
    ],
    [
        'name' => 'age',
        'type' => 'between',
        'label' => '?岁',
        'where' => "users.birth BETWEEN '?' AND '?'",
    ],
    [
        'name' => 'native',
        'type' => 'select',
        'label' => '本地户籍: ?',
        'option' => [
            '1' => '是',
            '0' => '否',
        ],
        'where' => [
            '1' => "users.native = 1",
            '0' => "users.native = 0",
        ],
    ],
    [
        'name' => 'glucose',
        'type' => 'select',
        'label' => '血糖: ?',
        'option' => [
            '1' => '正常',
            '2' => '偏高',
            '3' => '偏低',
            '4' => '很高',
            '5' => '很低',
        ],
        'where' => [
            '1' => "health_unique.glucose = 2",
            '2' => "health_unique.glucose = 1",
            '3' => "health_unique.glucose = 3",
            '4' => "health_unique.glucose = 0",
            '5' => "health_unique.glucose = 4",
        ],
    ],
    [
        'name' => 'pressure',
        'type' => 'select',
        'label' => '血压: ?',
        'option' => [
            '1' => '正常',
            '2' => '偏高',
            '3' => '偏低',
            '4' => '很高',
            '5' => '很低',
        ],
        'where' => [
            '1' => "health_unique.pressure = 2",
            '2' => "health_unique.pressure = 1",
            '3' => "health_unique.pressure = 3",
            '4' => "health_unique.pressure = 0",
            '5' => "health_unique.pressure = 4",
        ],
    ],
    [
        'name' => 'diabetes',
        'type' => 'checkbox',
        'label' => '病史: 糖尿病',
        'where' => "assess.diabetes = 2",
    ],
    [
        'name' => 'h_tension',
        'type' => 'checkbox',
        'label' => '病史: 高血压',
        'where' => "assess.h_tension = 2",
    ],
];
$query->setConf($aConf);
```

### 传入GET数组

```PHP
$query->setData($_GET);
```

### 获取SQL的where

```PHP
$query->whereStr();
// (users.account = '小刘' OR users.id_card = '小刘' OR users.name LIKE '%小刘%') AND (users.sex = 0) AND (assess.diabetes = 2)
```

### 返回选中的条件的链接

```PHP
$query->selectedLinks();
// <li class="am-fl"><span>病史: 糖尿病</span><a href="/client/lists?sex=0&search=%E5%B0%8F%E5%88%98" class="am-icon-times"></a></li><li class="am-fl"><span>性别: 女</span><a href="/client/lists?diabetes=1&search=%E5%B0%8F%E5%88%98" class="am-icon-times"></a></li><li class="am-fl"><span>搜索: 小刘</span><a href="/client/lists?diabetes=1&sex=0" class="am-icon-times"></a></li>
```

### 返回过滤后的GET数据

```PHP
$data = $query->data();
// 当你需求动态join数据表时，你可以根据key来判断
if(isset($data['pressure'])){
    $sql .= 'INNER JOIN health_unique ON health_unique.uid = users.uid ';
}
```

### 改造

框架已经搭建好了，实现思想你也明白了，你只需根据你的需求稍微修改一下。
