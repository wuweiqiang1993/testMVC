<?php

    class ItemController extends Controller
    {
        public function index()
        {
            $a = new ItemModel();
            var_dump($a->search());
        }
    }
    