<style>
.table{border-collapse:collapse;}
.table th,.table td{border:1px #ddd solid;padding:5px;}
.table td{text-align:center}
.style1 {color:#FF0000}
</style>

<h1>客户端下载及安装情况日报</h1>

<table class="table" width="800">
    <tr>
        <th width="100">日期</th>
        <th width="100">版本</th>
        <th>城市</th>
        <th>下载数</th>
        <th>下载数（按IP去重）</th>
        <th>安装数</th>
    </tr>
    <?php
        if(empty($download_and_install))
            return ;

        $total_download = 0; 
        $total_download_distinct_ip = 0; 
        $total_install = 0;
        foreach($download_and_install as $value)
        {
            echo "<tr>";
            echo "<td>{$value['DAY']}</td>";
            echo "<td>{$value['version']}</td>";
            echo "<td>{$value['city']}</td>";
            echo "<td>{$value['DOWNLOAD']}</td>";
            echo "<td>{$value['DISCOUNT']}</td>";
            echo "<td>{$value['INSTALL']}</td>";
            echo "</tr>";
            $total_download += intval($value['DOWNLOAD']);
            $total_download_distinct_ip += intval($value['DISCOUNT']);
            $total_install += intval($value['INSTALL']); 
        }
        echo "<tr>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td>{$total_download}</td>";
        echo "<td>{$total_download_distinct_ip}</td>";
        echo "<td>{$total_install}</td>";
        echo "</tr>";
    ?>
</table>

<br/><br/>

<h1>客户端安装及操作系统分布情况日报</h1>

<table class="table" width="800">
    <tr>
        <th width="100">日期</th>
        <th width="100">版本</th>
        <th width="100">操作系统</th>
        <th>城市</th>
        <th>安装数</th>
    </tr>
    <?php
        if(empty($install))
            return ;
        $total_install = 0; 
        foreach($install as $value)
        {
            echo "<tr>";
            echo "<td>{$value['DAY']}</td>";
            echo "<td>{$value['version']}</td>";
            echo "<td>{$value['os']}</td>";
            echo "<td>{$value['city']}</td>";
            echo "<td>{$value['INSTALL']}</td>";
            echo "</tr>";
            $total_install += intval($value['INSTALL']); 
        }
        echo "<tr>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td>{$total_install}</td>";
        echo "</tr>";
    ?>
</table>

<br/><br/>
