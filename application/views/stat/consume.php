
<style>
.table{border-collapse:collapse;}
.table th,.table td{border:1px #ddd solid;padding:5px;}
.table td{text-align:center}
</style>

<h1>SEM未达标客户名单(共计：<?php echo empty($data) ? 0 :count($data) ?>个)</h1>

<table class="table">
    <tr>
        <th>代理商</th>
        <th>分公司</th>
        <th>客服</th>
        <th>客户名称</th>
        <th>客户注册时间</th>
        <th>2014年日均消费(元)</th>
    </tr>
    <?php
        if(empty($data))
            return ;
        
        foreach($data as $value)
        {
            echo "<tr>";
            echo "<td>{$value['agency']}</td>";
            echo "<td>{$value['branch']}</td>";
            echo "<td>{$value['agent']}</td>";
            echo "<td>{$value['name']}</td>";
            echo "<td>{$value['ctime']}</td>";
            echo "<td>{$value['consume']}</td>";
            echo "</tr>";
        }
    ?>
</table>


