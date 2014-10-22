
<style>
.table{border-collapse:collapse;}
.table th,.table td{border:1px #ddd solid;padding:5px;}
.table td{text-align:center}
.style1 {color:#FF0000}
</style>

<h1>产品用户使用日报</h1>

<table class="table" width="1500">
    <tr>
        <th rowspan="2" width="100">日期</th>
        <th rowspan="2" width="150">代理商</th>
        <th rowspan="2">代理商区域</th>
        <th rowspan="2">客户数</th>
        <th rowspan="2">已登录未绑定客户数</th>
        <th rowspan="2">已绑定客户数</th>
        <th rowspan="2">激活竞价客户数</th>
        <th rowspan="2">当日已绑定登录客户数</th>
        <th colspan="4">推广数据</th>
        <th colspan="5">竞价相关数据</th>
        <th colspan="10">当日竞价关键词效果情况</th>
    </tr>
    <tr>
        <th>推广计划数</th>
        <th>推广单元数</th>
        <th>推广关键词数</th>
        <th>当日推广消费</th>
        <th>推广计划数</th>
        <th>推广单元数</th>
        <th>启用关键词数</th>
        <th>锁定对手的关键词数</th>
        <th>竞价暂停的关键词数</th>
        <th>展现次数</th>
        <th>展示占比</th>
        <th>点击次数</th>
        <th>点击占比</th>
        <th>消费情况</th>
        <th>消费占比</th>
        <th>点击率</th>
        <th>整体点击率</th>
        <th>平均点击价格</th>
        <th>整体平均点击价格</th>
    </tr>
    <?php
        if(empty($data))
            return ;
        
        foreach($data as $value)
        {
            echo "<tr>";
            echo "<td>{$value['date']}</td>";
            echo "<td>{$value['agency_name']}</td>";
            echo "<td>{$value['areas']}</td>";
            echo "<td>{$value['customer_amount']}</td>";
            echo "<td>{$value['login_not_bind']}</td>";
            echo "<td>{$value['bind_customer_amount']}</td>";
            echo "<td>{$value['active_amount']}</td>";
            echo "<td>{$value['bind_and_login']}</td>";
            echo "<td>{$value['plan_amount']}</td>";
            echo "<td>{$value['unit_amount']}</td>";
            echo "<td>{$value['keyword_amount']}</td>";
            echo "<td>{$value['cost']}</td>";
            echo "<td>{$value['bid_plan_amount']}</td>";
            echo "<td>{$value['bid_unit_amount']}</td>";
            echo "<td>{$value['bid_keyword_amount']}</td>";
            echo "<td>{$value['bid_lock_amount']}</td>";
            echo "<td>{$value['bid_pause_amount']}</td>";
            echo "<td>{$value['bid_impression_amount']}</td>";
            echo "<td>{$value['bid_impression_ratio']}</td>";
            echo "<td>{$value['bid_click_amount']}</td>";
            echo "<td>{$value['bid_click_ratio']}</td>";
            echo "<td>{$value['bid_cost_amount']}</td>";
            echo "<td>{$value['bid_cost_ratio']}</td>";
            echo "<td>{$value['bid_ctr']}</td>";
            echo "<td>{$value['ctr']}</td>";
            echo "<td>{$value['bid_acp']}</td>";
            echo "<td>{$value['acp']}</td>";
            echo "</tr>";
        }
    ?>
</table>

<br/><br/>
<p><label class="style1">客户数：</label>代理商下属所有的客户数<br/></p>
<p><label class="style1">已登录未绑定客户数：</label>已经登录过但是没有绑定百度推广账号的客户数<br/></p>
<p><label class="style1">已绑定客户数：</label>代理商下属所有已绑定过百度推广账号至智投易的客户数<br/></p>
<p><label class="style1">激活竞价客户数：</label>使用过智能竞价的客户数<br/></p>
<p><label class="style1">当日已绑定登录客户数：</label>已经绑定过百度推广账号至智投易且当日登录过的客户数<br/></p>



