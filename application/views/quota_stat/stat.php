
<style>
.table{border-collapse:collapse;}
.table th,.table td{border:1px #ddd solid;padding:5px;}
.table td{text-align:center}
</style>

<h1>MCC账号quota每日统计表</h1>

<table class="table">
    <tr>
        <th>账号</th>
        <th>近30天平均日消耗</th>
        <th>当日消耗</th>
        <th>周平均日消耗</th>
        <th>本周最高日消耗</th>
        <th>本周最低日消耗</th>
        <th>预计可支持用户数</th>
        <th>本周已使用额度</th>
        <th>本周仍剩余额度</th>
        <th>使用状态</th>
    <tr>
    <?php
        if(empty($data))
            return ;
        
        foreach($data as $value)
        {
            echo "<tr>";
            echo "<td>{$value['username']}</td>";
            echo "<td>{$value['month_avg']}</td>";
            echo "<td>{$value['consume']}</td>";
            echo "<td>{$value['week_avg']}</td>";
            echo "<td>{$value['week_max']}</td>";
            echo "<td>{$value['week_min']}</td>";
            echo "<td>{$value['predict_user_amount']}</td>";
            echo "<td>{$value['week_sum']}</td>";
            echo "<td>{$value['week_balance']}</td>";
            echo "<td>{$value['status']}</td>";
            echo "</tr>";
        }
    ?>
</table>


