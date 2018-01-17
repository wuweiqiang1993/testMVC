<?php
class Request
{
    public $POST = array();
    public $GET = array();
    public $COOKIE = array();

    public function __construct()
    {
        $this->clearPOST();
        $this->clearGET();
        $this->clearCOOKIE();
    }

    private function clearPOST()
    {
        if (count($_POST)==0) { 
            return ;
        }
        $this->POST = $this->clearData($_POST);
    }

    private function clearGET()
    {
        if (count($_GET)==0) { 
            return ;
        }
        $this->GET = $this->clearData($_GET);
    }

    private function clearCOOKIE()
    {
        if (count($_COOKIE)==0) { 
            return ;
        }
        $this->COOKIE = $this->clearData($_COOKIE);
    }

    public function clearData($input)
    {
        $new_input = array();
        foreach ($input as $key => $value) {
            if(is_array($value)){
                $new_input[$key] = $this->clearData($value);
                continue;
            }
            $value = $this->new_addslashes($value);
            $value = $this->new_stripslashes($value);
            $value = $this->new_html_special_chars($value);
            $value = $this->new_html_entity_decode($value);
            $value = $this->new_htmlentities($value);
            $value = $this->safe_replace($value);
            $value = $this->remove_xss($value);
            $value = $this->trim_unsafe_control_chars($value);
            $value = $this->trim_textarea($value);
            $value = $this->trim_script($value);
            $new_input[$key] = $value;
        }
        return $new_input;
    }
    /**
     * 返回经addslashes处理过的字符串或数组
     * @param $string 需要处理的字符串或数组
     * @return mixed
     */
    public function new_addslashes($string)
    {
        if (!is_array($string)) {
            return trim(addslashes($string));
        }

        foreach ($string as $key => $val) {
            $string[$key] = new_addslashes($val);
        }

        return $string;
    }

    /**
     * 返回经stripslashes处理过的字符串或数组
     * @param $string 需要处理的字符串或数组
     * @return mixed
     */
    public function new_stripslashes($string)
    {
        if (!is_array($string)) {
            return stripslashes($string);
        }

        foreach ($string as $key => $val) {
            $string[$key] = new_stripslashes($val);
        }

        return $string;
    }

    /**
     * 返回经htmlspecialchars处理过的字符串或数组
     * @param $obj 需要处理的字符串或数组
     * @return mixed
     */
    public function new_html_special_chars($string)
    {
        if (!is_array($string)) {
            return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
        }

        foreach ($string as $key => $val) {
            $string[$key] = new_html_special_chars($val);
        }

        return $string;
    }

    public function new_html_entity_decode($string)
    {
        return html_entity_decode($string, ENT_QUOTES, 'utf-8');
    }

    public function new_htmlentities($string)
    {
        return htmlentities($string, ENT_QUOTES, 'utf-8');
    }

    /**
     * 安全过滤函数
     *
     * @param $string
     * @return string
     */
    public function safe_replace($string)
    {
        $string = str_replace('%20', '', $string);
        $string = str_replace('%27', '', $string);
        $string = str_replace('%2527', '', $string);
        $string = str_replace('*', '', $string);
        $string = str_replace('"', '&quot;', $string);
        $string = str_replace("'", '&apos;', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('<', '&lt;', $string);
        $string = str_replace('>', '&gt;', $string);
        $string = str_replace("{", '', $string);
        $string = str_replace('}', '', $string);
        $string = str_replace('\\', '', $string);
        return $string;
    }

    /**
     * xss过滤函数
     *
     * @param $string
     * @return string
     */
    public function remove_xss($string)
    {
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);

        $parm1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');

        $parm2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');

        $parm = array_merge($parm1, $parm2);

        for ($i = 0; $i < sizeof($parm); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($parm[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0([9][a][b]);?)?';
                    $pattern .= '|(&#0([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $parm[$i][$j];
            }
            $pattern .= '/i';
            $string = preg_replace($pattern, ' ', $string);
        }
        return $string;
    }

    /**
     * 过滤ASCII码从0-28的控制字符
     * @return String
     */
    public function trim_unsafe_control_chars($str)
    {
        $rule = '/[' . chr(1) . '-' . chr(8) . chr(11) . '-' . chr(12) . chr(14) . '-' . chr(31) . ']*/';
        return str_replace(chr(0), '', preg_replace($rule, '', $str));
    }

    /**
     * 格式化文本域内容
     *
     * @param $string 文本域内容
     * @return string
     */
    public function trim_textarea($string)
    {
        $string = nl2br(str_replace(' ', '&nbsp;', $string));
        return $string;
    }


    /**
     * 转义 javascript 代码标记
     *
     * @param $str
     * @return mixed
     */
    public function trim_script($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = trim_script($val);
            }
        } else {
            $str = preg_replace('/\<([\/]?)script([^\>]*?)\>/si', '&lt;\\1script\\2&gt;', $str);
            $str = preg_replace('/\<([\/]?)iframe([^\>]*?)\>/si', '&lt;\\1iframe\\2&gt;', $str);
            $str = preg_replace('/\<([\/]?)frame([^\>]*?)\>/si', '&lt;\\1frame\\2&gt;', $str);
            $str = str_replace('javascript:', 'javascript：', $str);
        }
        return $str;
    }
}