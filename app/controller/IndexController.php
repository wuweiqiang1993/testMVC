<?php

    class IndexController extends Controller
    {
        public function index()
        {
            print_r((function (array $arr):array{
                return array_filter($arr,
                    function ($k) use ($arr){
                        return substr_count(implode('',$arr),$arr[$k])===1;
                    },ARRAY_FILTER_USE_KEY
                );
            })(['不知名互联网资讯博主','知名互联网资讯博主','头条文章作者','资讯博主']));
            $a = new ItemModel();
            $this->assign('name',$a->search());
            $this->display('index.html');
        }
    }
    