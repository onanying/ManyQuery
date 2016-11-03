<?php

/**
 * 多条件查询处理类
 * @author 刘健 <59208859@qq.com>
 */
class ManyQuery
{

    // 配置数组
    protected $aConf = [
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

    protected $aData = []; // GET数组

    // 传入配置
    public function setConf($aConf)
    {
        $this->aConf = $aConf;
    }

    // 设置GET数据
    public function setData($aGet)
    {
        // 过滤非法数据
        $aTmp = [];
        foreach ($aGet as $sKey => $sValue) {
            foreach ($this->aConf as $aItem) {
                if ($sKey == $aItem['name']) {
                    if ($aItem['type'] == 'select') {
                        if (in_array($sValue, array_keys($aItem['option']))) {
                            $aTmp[$sKey] = $sValue;
                        }
                    } else {
                        $aTmp[$sKey] = $sValue;
                    }
                    break;
                }
            }
        }
        // 赋值
        $this->aData = $aTmp;
    }

    // 返回过滤后的GET数据
    public function data()
    {
        return $this->aData;
    }

    // 获取标签名
    protected function label($sName)
    {
        $sLabel = '';
        foreach ($this->aConf as $aItem) {
            if ($sName == $aItem['name']) {
                $sType = $aItem['type'];
                $sLabel = $aItem['label'];
                $sValue = $this->aData[$sName];
                switch ($sType) {
                    case 'between':
                        $sValue = explode(',', $sValue);
                        $text = "{$sValue[0]}~{$sValue[1]}";
                        if ($sValue[0] == '') {
                            $text = "<={$sValue[1]}";
                        }
                        if ($sValue[1] == '') {
                            $text = ">={$sValue[0]}";
                        }
                        $sLabel = str_ireplace('?', $text, $sLabel);
                        break;
                    case 'select':
                        $aOption = $aItem['option'];
                        $sLabel = str_ireplace('?', $aOption[$sValue], $sLabel);
                        break;
                    case 'checkbox':
                        $sLabel = $sLabel;
                        break;
                    case 'search':
                        $sLabel = str_ireplace('?', $sValue, $sLabel);
                        break;
                }
            }
        }
        return $sLabel;
    }

    // 选择的链接
    public function selectedLinks()
    {
        // 构建数据
        parse_str($_SERVER['QUERY_STRING'], $aParams);
        $aHtml = [];
        foreach ($this->aData as $sKey => $sValue) {
            $aTmp = $aParams;
            unset($aTmp[$sKey]);
            $aHtml[$sKey] = http_build_query($aTmp);
        }
        // 生成html
        $sHtml = '';
        foreach ($aHtml as $sKey => $sValue) {
            $sHtml .= '<li class="am-fl"><span>' . $this->label($sKey) . '</span><a href="/client/lists' . ($sValue == '' ? $sValue : '?' . $sValue) . '" class="am-icon-times"></a></li>';
        }
        // 返回
        return $sHtml;
    }

    // SQL的where条件
    public function whereStr()
    {
        $aSql = [];
        foreach ($this->aConf as $aItem) {
            $sKey = $aItem['name'];
            $sType = $aItem['type'];
            $sWhere = $aItem['where'];
            $sValue = isset($this->aData[$sKey]) ? $this->aData[$sKey] : null;
            if (!is_null($sValue)) {
                switch ($sType) {
                    case 'between':
                        $sWhere = str_ireplace('?', '%s', $sWhere);
                        $sValue = explode(',', $sValue);
                        $sValue[1] = date('Y') - ($sValue[1] == '' ? '200' : $sValue[1]);
                        $sValue[0] = date('Y') - ($sValue[0] == '' ? '1' : $sValue[0]);
                        $aSql[] = '(' . sprintf($sWhere, $sValue[1] . '-01-01', $sValue[0] . '-12-31') . ')';
                        break;
                    case 'select':
                        $sWhere = $sWhere[$sValue];
                        $aSql[] = '(' . $sWhere . ')';
                        break;
                    case 'checkbox':
                        $aSql[] = '(' . $sWhere . ')';
                        break;
                    case 'search':
                        $aSql[] = '(' . str_ireplace('?', $sValue, $sWhere) . ')';
                        break;
                }
            }
        }
        return implode(' AND ', $aSql);
    }

}
