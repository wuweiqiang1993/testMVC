<?php
/*
分页类
例子：连表查询分页
include '../models/DB.class.php';
$b = new DB(array('DB_NAME'=>'im_oaeo'));
$count = $b->table('user','a')->join('user_group','b')->on('a.user_ID=b.userid')->join('(select * from auth_user_role group by userid)','c')->on('a.user_ID=c.userid')->where('group_id=\'21\'')->count();
$a = new Pages($count, $_GET['page'],20);
$limit =  $a->limit();//sql语句的limit部分
$result = $b->table('user','a')->join('user_group','b')->on('a.user_ID=b.userid')->join('(select * from auth_user_role group by userid)','c')->on('a.user_ID=c.userid')->where('group_id=\'21\'')->limit($limit)->select();
echo $a->showpage();//分页的html部分
dump($result);*/



class Pages
{
    //记录总条数
    protected $total;
    //每页显示几条
    protected $nums;
    //总页数
    protected $totalPages;
    //当前页码
    protected $currentPage;
    //上一页页码
    protected $prevPage;
    //下一页页码
    protected $nextPage;
    //首页页码
    protected $firstPage = 1;
    //尾页页码
    protected $endPage;
    //url
    protected $url;
    //limit,传到数据库的limit
    protected $limit;

    //构造函数，初始化
    public function __construct($total = 1, $currentPage = 1, $nums = 15)
    {
        $this->total = $total;
        $this->nums = $nums;
        $this->totalPages = $this->getTotalPages();
        $this->currentPage = $this->getCurentPage($currentPage);
        $this->getPrevPage();
        $this->getNextPage();
        $this->getEndPage();
        $this->setUrl();
    }
    protected function getCurentPage($currentPage)
    {
        //判断如果存在page参数并且page大于0，返回实际值，否则返回1
        if (isset($currentPage) && intval($currentPage) > 0) {
            $this->currentPage = intval($currentPage);
            if ($this->currentPage > $this->totalPages) {
                $this->currentPage = $this->totalPages;
            }
        } else {
            $this->currentPage = 1;
        }
        return $this->currentPage;
    }
    protected function getTotalPages()
    {
        return ceil($this->total / $this->nums);
    }

    protected function getPrevPage()
    {
        $this->prevPage = $this->currentPage - 1;
        if ($this->prevPage < 1) {
            $this->prevPage = 1;
        }
        return $this->prevPage;
    }
    protected function getNextPage()
    {
        $this->nextPage = $this->currentPage + 1;
        if ($this->nextPage > $this->totalPages) {
            $this->nextPage = $this->totalPages;
        }
        return $this->nextPage;
    }

    protected function getEndPage()
    {
        $this->endPage = $this->totalPages;
        return $this->endPage;
    }

    public function limit()
    {
        return ($this->currentPage - 1) * $this->nums . ',' . $this->nums;
    }
    public function setUrl()
    {
        //获取当前页面的文件位置
        $url = $_SERVER['REQUEST_URI'];
        //将url参数解析成数组
        $parse = parse_url($url);
        //获得域名地址
        $path = $parse['path'];
        //获取参数
        $query = isset($parse['query']) ? $parse['query'] : false;
        //如果有参数，把page这个参数先给干掉，因为我们要重新拼接
        if ($query) {
            parse_str($query, $query);
            //干掉page参数，保留其他参数
            unset($query['page']);
            //http_build_query拼将参数拼接成请求
            $uri = $parse['path'] . '?' . http_build_query($query);
        } else {
            $uri = rtrim($parse['path'], '?') . '?';
        }

        //智能识别https和http协议和端口号
        $protocal = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        switch ($_SERVER['SERVER_PORT']) {
            case 80:
            case 443:
                $uri = $protocal . $_SERVER['SERVER_NAME'] . $uri;
                break;
            default:
                $uri = $protocal . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $uri;
                break;
        }
        $this->url = $uri;
    }
    public function createUrl($page)
    {
        if (substr($this->url, -4) == '.php') {
            return $this->url . '?page=' . $page;
        }
        if (substr($this->url, -1) == '?') {
            return $this->url . 'page=' . $page;
        }
        return $this->url . '&page=' . $page;
    }
    public function showpage()
    {   
        if($this->total==0){
            return '<div class="pagination">暂无相关数据</div>';
        }
        $first = "<a class='page firstpage' href='{$this->createUrl($this->firstPage)}'>首页</a>&nbsp;";
        $prev = "<a class='page prevpage' href='{$this->createUrl($this->prevPage)}'>上一页</a>&nbsp;";
        $current = "<span class='page currentpage'>第{$this->currentPage}页</span>&nbsp;";
        $next = "<a class='page nextpage' href='{$this->createUrl($this->nextPage)}'>下一页</a>&nbsp;";
        $end = "<a class='page endpage' href='{$this->createUrl($this->endPage)}'>末页</a>&nbsp;";
        $total = "共{$this->total}条记录&nbsp;每页{$this->nums}条&nbsp;共{$this->totalPages}页";
        return '<div class="pagination">' . $first . $prev . $current . $next . $end . $total . '</div>';
    }
}