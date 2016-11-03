<?php

/**
 * 多条件查询处理类
 * @author 刘健 <59208859@qq.com>
 */
class ManyQuery
{
    
    protected $aConf = []; // 配置数组

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
