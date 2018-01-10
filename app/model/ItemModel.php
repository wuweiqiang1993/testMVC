<?php

/**
 * 用户Model
 */
class ItemModel extends Model
{
    /**
     * 自定义当前模型操作的数据库表名称，
     * 如果不指定默认为类名称的小写字符串，
     * 这里就是 item 表
     * @var string
     */
    public $_table = 'item';

    /**
     * 搜索功能，因为Sql父类里面没有现成的like搜索，
     * 所以需要自己写SQL语句，对数据库的操作应该都放
     * 在Model里面，然后提供给Controller直接调用
     * @param $title string 查询的关键词
     * @return array 返回的数据
     */
    public function search()
    {
        $sql = "select * from `$this->_table`";
        $sth = $this->_dbHandle->prepare($sql);
        $sth->execute();

        return $sth->fetchAll();
    }

    public function hongbao(float $total_money,int $people_num,float $min,float $max):array
    {
        $result = [];
        $left_money = $total_money-$people_num*$min;
        $min==0&&$min=0.01;
        if($left_money<0){
            exit('总数不足以分配最低额度');
        }
        if($total_money>$people_num*$max){
            exit('总数太高无法满足分配区间需求');
        }
        //满足区间的最高额度
        if($total_money==$people_num*$max){
            for ($i=0; $i < $people_num; $i++) { 
                $result[$i]=$max;
            }
            return $result;
        }
        //满足区间的最低额度
        if($total_money==$people_num*$min){
            for ($i=0; $i < $people_num; $i++) { 
                $result[$i]=$min;
            }
            return $result;
        }
        //正常情况
        $field = $max-$min;
        $i=0;
        while($i < $people_num) { 
            $left_num = $people_num-$i-1;//未分配人数
            if($left_num==0){
                unset($result[$i]);
                $result[$i] = round(($total_money*100-array_sum($result)*100)/100,2);
                break;
            }
            $result[$i] = $min+round(mt_rand(0, $field*100)/100,2);
            $res_sum = array_sum($result);//已分配总数
            $left = ($total_money*100-$res_sum*100)/100;//剩余钱数
            //echo ($i+1).' 结果：'.$result[$i].' 剩余：'.$left.'<br>';
            $left_per = $left/$left_num;
            if($left_per>=$min&&$left_per<=$max){
                $i++;
            }
        }
        return $result;
    }

    public function WXhongbao(float $totalMoney,int $peopleNum):array
    {
        $result = [];
        $totalMoney<$peopleNum*0.01&&exit('总金额'.$totalMoney.'不满足'.$peopleNum.'人的最少分配金额0.01元');
        $min = 0.01;
        $max = $totalMoney-($peopleNum-1)*0.01;
        //满足区间的最低额度
        if($totalMoney==$peopleNum*$min){
            for ($i=0; $i < $peopleNum; $i++) { 
                $result[$i]=$min;
            }
            return $result;
        }
        for ($i=0; $i < $peopleNum; $i++) { 
            $left_num = $peopleNum-$i-1;//未分配人数
            if($left_num==0){
                $result[$i] = round(($totalMoney*100-array_sum($result)*100)/100,2);
                break;
            }
            $result[$i] = round(mt_rand($min*100, $max*100)/100,2);
            $res_sum = array_sum($result);//已分配总数
            $max = round(($totalMoney*100-$res_sum*100-$left_num*0.01*100)/100,2);//剩余钱数
            //echo '总数'.($totalMoney*100).' 已分：'.($res_sum*100).' 剩余：'.($max).'<br>';
        }
        return $result;
    }
}