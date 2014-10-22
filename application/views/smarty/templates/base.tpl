{include file="functions.tpl" scope=parent inline}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>智投易</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
{block headercss}

    {if $environment == 'production'}
    <link href="/static/??plugins/bootstrap/css/bootstrap.css,plugins/jquery-tags-input/jquery.tagsinput.css,plugins/bootstrap-daterangepicker/daterangepicker.css,plugins/bootstrap-modal/css/bootstrap-modal.css,css/csem.css?v={$version}" rel="stylesheet" type="text/css"/>
    {else}
    <link href="/static/plugins/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
	<link href="/static/plugins/jquery-tags-input/jquery.tagsinput.css" rel="stylesheet" type="text/css">
    <link href="/static/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
    <link href="/static/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
    <link href="/static/css/csem.css?v={$version}" rel="stylesheet" type="text/css"/>
    {/if}
{/block}
    <!--link rel="shortcut icon" href="/static/favicon.ico" /-->
</head>
<body class="page-header-fixed {block login_body}{/block}">
<div class="transition" id="page_content">
{block content}{/block}
<a data-toggle="modal" href="#updateLayer" class="open-layer"></a>
<a data-toggle="modal" href="#updateLayer" class="open-err-layer"></a>
<input id="wtc" type="hidden" value="">
<input id="g_ids" type="hidden" value="">
<input id="g_times" type="hidden" value="">
</div>
{block footerjs}
{if $environment == 'production'}
<script src="/static/??plugins/jquery-1.11.0.min.js,plugins/jquery-migrate-1.2.1.min.js,plugins/jquery-ui/jquery-ui-1.10.1.custom.min.js,plugins/bootstrap/js/bootstrap.min.js,plugins/jquery.cookie.min.js,plugins/highcharts/highcharts.src.js,plugins/bootstrap-modal/js/bootstrap-modal.js,plugins/bootstrap-modal/js/bootstrap-modalmanager.js,plugins/bootstrap-daterangepicker/date.js,plugins/bootstrap-daterangepicker/daterangepicker.js,plugins/jquery-tags-input/jquery.tagsinput.js,plugins/juicer-min.js,scripts/area.js,scripts/base.js?v={$version}" type="text/javascript"></script>
{else}
<script src="/static/plugins/jquery-1.11.0.min.js" type="text/javascript"></script>
<script src="/static/plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script src="/static/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/static/plugins/jquery.cookie.min.js" type="text/javascript"></script>
<script src="/static/plugins/highcharts/highcharts.src.js" type="text/javascript" ></script>
<script src="/static/plugins/bootstrap-modal/js/bootstrap-modal.js" type="text/javascript" ></script>
<script src="/static/plugins/bootstrap-modal/js/bootstrap-modalmanager.js" type="text/javascript" ></script>
<script src="/static/plugins/bootstrap-daterangepicker/date.js" type="text/javascript"></script>
<script src="/static/plugins/bootstrap-daterangepicker/daterangepicker.js" type="text/javascript"></script>
<script src="/static/scripts/juicer-min.js" type="text/javascript"></script>
<script type="text/javascript" src="/static/plugins/jquery-tags-input/jquery.tagsinput.js"></script>
<script type="text/javascript" src="/static/scripts/area.js?v={$version}"></script>
<script type="text/javascript" src="/static/scripts/base.js?v={$version}"></script>
{/if}
{block customjs}
<script type="text/javascript" src="/static/scripts/sem.js?v={$version}"></script>
{/block}
{/block}
</body>
</html>
