<?php

    class IndexController extends Controller
    {
        public function index()
        {
            $a = new ItemModel();
            var_dump($a->search());
        }
    }
    